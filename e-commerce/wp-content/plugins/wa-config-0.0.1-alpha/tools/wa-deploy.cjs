/*
* Will connect to wa-config rest server and deploy
*
* This script demonstrate usages of wa-config-by-monwoo REST deploy API
*
* It's not suited for huge uploads (Upload of 200Mo zip file may fail...)
*
* 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
* service@monwoo.com
*/

const fs = require('fs');
// https://stackabuse.com/making-http-requests-in-node-js-with-node-fetch
// import fetch from "node-fetch";
const fetch = require('node-fetch');
// TODO : jest tests ? Humanly tested OK for now.
// https://stackoverflow.com/questions/58648691/mocking-node-fetch-with-jest-creating-a-response-object-for-mocking
// const { Response, Headers } = jest.requireActual('node-fetch'); 
const { Response, Headers } = fetch;
const https = require('https');
const FormData = require('form-data');
const readline = require("readline");
// const {Base64Encode} = require("base64-stream"); // npm install -D base64-stream // Required: {"node":"18.x"}
const path = require("path");
const Cryptr = require('cryptr');

// https://www.npmjs.com/package/dotenv
// https://coderrocketfuel.com/article/how-to-load-environment-variables-from-a-.env-file-in-nodejs
require('dotenv').config();
const secuEnvConfigPath = process.env.WA_SECU_ENV_CONFIG || '.wa-secu.env'
require('dotenv').config( { path: secuEnvConfigPath} );
// console.log(process.env); // remove this after you've confirmed it's working

// https://stackoverflow.com/questions/26156292/trim-specific-character-from-a-string
const waApiBaseUrl = (process.env.WA_REST_API_SERVER
|| 'error:// # unknow-env-WA_REST_API_SERVER #').replace(/\/+$/g, ''); // .trim('/');
const waApiUserLocation = process.env.WA_USER_LOCATION
|| '# unknow-env-WA_USER_LOCATION #';

// https://stackoverflow.com/questions/3746725/how-to-create-an-array-containing-1-n
// for(var i=32;i<127;++i) console.log(String.fromCharCode(i));
// const ascii = range(32, 126).map((c) => String.fromCharCode(c));

// https://stackoverflow.com/questions/1349404/generate-random-string-characters-in-javascript
function rand_string(length) {
    var result           = '';
    // var characters       = ascii;
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_/?^*{}()[]';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}

// range(9, 18); // [9, 10, 11, 12, 13, 14, 15, 16, 17, 18]
function range(start, end) {
    return Array(end - start + 1).fill().map((_, idx) => start + idx)
}

function toBool(mixed) {
    // TODO : parse env to real values ?
    // https://stackoverflow.com/questions/263965/how-can-i-convert-a-string-to-boolean-in-javascript
    // $.parseJSON("TRUE".toLowerCase()); sound the best generic, but heavy ? 
    return (typeof mixed === 'string' || mixed instanceof String)
    ? JSON.parse(mixed.toLowerCase()) : Boolean(mixed);
}

let [
    head_target, zip_subpath, zip_bundle,
    isDebug, isDebugVerbose, isDebugVeryVerbose,
    allowSslSelfSignedCertificates,
    shouldEncrypt, encryptSalt
] = [
    process.env.WA_HEAD_TARGET || "unknow-env-WA_HEAD_TARGET",
    process.env.WA_ZIP_SUBPATH || "",
    process.env.WA_ZIP_ARCHIVEPATH || "build/static.zip",

    toBool(process.env.DEBUG),
    toBool(process.env.DEBUG_VERBOSE),
    toBool(process.env.DEBUG_VERY_VERBOSE),

    toBool(process.env.WA_SSL_ALLOW_SELFSIGNED),

    toBool(process.env.WA_SHOULD_ENCRYPT || true),
    process.env.WA_ENCRYPT_SALT || null,
];

function error(e, ...ctx) {
    console.error(`ERROR : ${e}`, ...ctx);
    throw new Error(e);
}

function assert(test, msg, ...ctx) {
    if (!test) {
        console.warn(`Assertion failed : ${msg}`, ...ctx);
        throw new Error(msg);    
    }
}

function debug(...ctx) {
    if (isDebug) {
        const prompt = `[${new Date()}]`;
        // console.debug(prompt, ...ctx);
        console.debug("");
        console.debug(prompt);
        ctx.forEach(function (log) {
            console.debug("    ", log);
        });
        console.debug("");
    }
}

function debugVerbose(...ctx) {
    if (isDebugVerbose) {
        debug(...ctx);
    }
}

// Soft assert example :
// console.assert(waApiBaseUrl, "Missing WA_REST_API_SERVER env, check your .env file.");
assert(waApiBaseUrl, "Missing WA_REST_API_SERVER env, check your .env file.");
assert(waApiUserLocation, "Missing WA_USER_LOCATION env, check your .env file.");

debug("Will deploy from : ", waApiBaseUrl, waApiUserLocation)

const deployUrl = `${waApiBaseUrl}/fronthead/sync`;
debug("To url : ", deployUrl);

if (!encryptSalt) {
    encryptSalt = rand_string(42);
    debug(`Save new encrypting secu key to ${secuEnvConfigPath}`);
    // TODO : mege configs system ? Only one secu config for now. Ok, for now.
    fs.writeFileSync(secuEnvConfigPath, `WA_ENCRYPT_SALT=${encryptSalt}`);
}
// https://www.npmjs.com/package/cryptr
const cryptr = new Cryptr(encryptSalt);

// https://nodejs.dev/learn/writing-files-with-nodejs
const secuTmpFile = '.wa-access.tmp';

let waAccess = {};
try {
    if (fs.existsSync(secuTmpFile)) {
        let accessData = fs.readFileSync(secuTmpFile, waAccess);
        if (shouldEncrypt) {
            // https://www.npmjs.com/package/cryptr
            const cryptr = new Cryptr(encryptSalt);
            accessData = cryptr.decrypt(accessData);
        }
        waAccess = JSON.parse(accessData);
    }
} catch (err) {
    console.error(err);
    waAccess = {};
    fs.rmSync(secuTmpFile);
}

// const headers = new Headers({
const defaultHeaders = () => ({
    accept: 'application/json',
    ... (waAccess.nonce ? { 'X-WP-Nonce': waAccess.nonce } : {}),
    ... (waAccess.quick_COOKIE ? { cookie: waAccess.quick_COOKIE } : {}),
});

const defaultPostData = () => ({
    sync_action: 'publish',
    user_location: waApiUserLocation,
    wa_access_id: waAccess.wa_access_id,
    wa_api_pre_fetch_token: waAccess.wa_api_pre_fetch_token,
});

// 🌖🌖 Request : wa-config-by-monwoo deploy api  🌖🌖
// 🌖🌖           with build at build/static.zip  🌖🌖

const deploy_init = (infinitRetrySentinelCount = 4) => {
    if (infinitRetrySentinelCount < 0) {
        debug("infinitRetrySentinelCount reached, STOPPING deploy_init.");
        error("Fail deploy INIT.");
        return { code : "infinit_retry_sentinel_count_reached_error" } ;
    }

    const headers = {
        ...defaultHeaders(),
    };
    debug("With headers data : ", headers);
    
    // https://gist.github.com/pinkhominid/e6f53706e0dd8cf34f2bd94c3aa357c5
    const postData = new FormData();

    // loading zip file 
    // TODO : 406 error solved with curl using "zip_bundle='@tmp.zip;type=application/zip'", but fail with below :
    // const stats = fs.statSync(zip_bundle);
    // const fileSizeInBytes = stats.size;
    // const fileStream = fs.createReadStream(zip_bundle);
    // postData.append('zip_bundle', fileStream, {
    //     // contentType: 'application/zip', // This is for request Headers / Full encoding, per file it's 'type' arg
    //     type: 'application/zip',
    //     name: path.basename(zip_bundle),
    //     knownLength: fileSizeInBytes,
    // }); // TODO : Base64Encode for node >18, how in earlier version of node ? + dev env node install deps needed, bad for simple script...
    // const b64Stream = fileStream.pipe(new Base64Encode());
    // postData.append('zip_bundle_b64', < ... b64Stream ... >);
    const zipBuffer = fs.readFileSync(zip_bundle);
    
    // utf8.decode(base64.decode(base64Str));
    // const b64Buffer = base64.encode(utf8.encode(zipBuffer));

    // https://stackoverflow.com/questions/6182315/how-can-i-do-base64-encoding-in-node-js
    const b64Buffer = Buffer.from(zipBuffer).toString('base64')

    postData.append('zip_bundle_b64', b64Buffer);

    // TODO : 
    // loading extra params
    const extra = {
        ...defaultPostData(),
        head_target,
        zip_subpath,
        zip_bundle,
    };
    Object.keys(extra).forEach(k => {
        // console.log("key", k);
        // Err : Cannot read properties of undefined (reading 'name')
        // if append undefined values to postData..., if check to avoid it
        if (extra[k] && extra[k].length) {
            postData.append(k, extra[k]);
        }
    });
    debugVerbose("With post data : ", postData);

    // https://stackoverflow.com/questions/52478069/node-fetch-disable-ssl-verification
    const permissiveHttpsAgent = new https.Agent({
        rejectUnauthorized: false, // Allow api call on self signed certificates, for dev purpose
    });

    // Try to deploy
    return fetch(deployUrl, {
        method: 'POST',
        body: postData,
        headers: headers,
        ...(allowSslSelfSignedCertificates ? { agent: permissiveHttpsAgent } : {} ),
    })
    // Handle network or bad url errors :
    .catch(err => {
        debug(err);
        const resp = new Response(JSON.stringify({
            code: 'wa-deploy_network_fetch_err',
            message: `Fetch ERROR for : '${deployUrl}'`,
            data: {
                error: err,
                status: 404,
            }
        }), {
            status: 404,
            statusText: 'fail',
            headers: new Headers({ 'Content-Type': 'application/json' }),
        });
        return resp;
    })
    // Debug
    .then(res => res.clone().text().then(
        t => debug("Did fetch : ", t)
    ) && res)
    // Json extract
    .then(res => res.json())
    // Test error and take appropriate actions
    .then(res => {
        let resp = res;
        if ('wa_api_login_required' === res.error) {
            const authLink = res.location;
            // https://nodejs.org/en/knowledge/command-line/how-to-prompt-for-command-line-input
            const rl = readline.createInterface({
                input: process.stdin,
                output: process.stdout
            });
            resp = new Promise((resolve, reject) => {
                rl.question(`Did you succed to authenticate with :\n${authLink}\n? (y/n)`, userResp => {
                    if ('n' === userResp) {
                        rl.question(`Deploy will fail, should re-try \n${authLink}\n? (y/n) `, userResp => {
                            if ('y' === userResp) {
                                resolve({
                                    ...res,
                                    should_retry: true,
                                });
                            } else {
                                reject(res);
                            }
                            rl.close();
                        });    
                    } else {
                        rl.close();
                        resolve({
                            ...res,
                            should_retry: true,
                        });    
                    }
                });
            });          
        }
        return resp;
    })
    // Debug
    .then(resp => debugVerbose("Did load : ", resp) || resp)
    // Retry if should_retry is asked, no downgrade infinit sentinel since it's user choice to re-try
    .then(resp => resp.should_retry ? deploy_init() : resp)
    // Error handler
    .catch(err => {
        console.error('Error :', err);
        return err;
    })
    // Update internals from response
    .then(resp => {
        if ('wa_auth_denied_since_wp_auth_denied' === resp.code) {
            waAccess = {}; // clean our waAccess since we have some wrong value in it making server loop on bad access...
        }

        wa_api_pre_fetch_token = (resp.data?.wa_api_pre_fetch_token && resp.data?.wa_api_pre_fetch_token.length)
        ? resp.data?.wa_api_pre_fetch_token : waAccess.wa_api_pre_fetch_token;

        wa_access_id = (resp.data?.wa_access_id && resp.data?.wa_access_id.length)
        ? resp.data?.wa_access_id : waAccess.wa_access_id;

        quick_COOKIE = (resp.data?.quick_COOKIE && resp.data?.quick_COOKIE.length)
        ? resp.data?.quick_COOKIE : waAccess.quick_COOKIE;

        nonce = (resp.data?.nonce && resp.data?.nonce.length)
        ? resp.data?.nonce : waAccess.nonce;

        waAccess = {
            ...waAccess,
            wa_api_pre_fetch_token,
            wa_access_id,
            quick_COOKIE,
            nonce,
        }

        debug("Did update waAccess : ", waAccess);

        return resp;
    }).then(
        resp => 'wrong_auth_header_or_cookie' === resp.code
        ? deploy_init(infinitRetrySentinelCount - 1) // This error will fill up our credentials, next call should upload the zip
        : resp
    );
}

const deploy = () => deploy_init()
.then(resp => {
    console.log(resp);

    // Save headers, cookies, auth, etc...
    // re-load deploy_init with updated headers will launch it right ;)
    try {
        let serializableWaAccess = JSON.stringify(waAccess);
        if (shouldEncrypt) {
            serializableWaAccess = cryptr.encrypt(serializableWaAccess);
        }

        fs.writeFileSync(secuTmpFile, serializableWaAccess);
    } catch (err) {
        console.error(err);
    }    
})

// Launch the deploy process
deploy();