<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.13  |
    |              on 2022-03-04 09:42:17              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
/*
Copyright Monwoo 2021, build by Miguel Monwoo, service@monwoo.com
*/
 header("\x53\x74\162\x69\x63\164\x2d\124\x72\141\156\x73\160\157\x72\x74\55\x53\145\143\x75\x72\151\x74\171\x3a\40\x6d\141\170\55\141\x67\x65\x3d\x30\x3b"); $ZdalG = __DIR__ . "\57\166\145\x6e\144\157\162\57\x61\x75\x74\x6f\x6c\157\141\x64\x2e\x70\150\x70"; if (!file_exists($ZdalG)) { goto G7_Jf; } include $ZdalG; G7_Jf: use Monolog\Logger; use Monolog\Handler\StreamHandler; use Monolog\Formatter\LineFormatter; goto W2Akt; ngxza: $fYkmf->configure()->fetchSourceRequest()->renderView(); goto X0rMg; r1yBy: class Proxy { public $proxify = []; public $dstUpdateView = null; public function __construct(array $proxify = []) { goto jG51L; Vysnr: $V0I1Y = new LineFormatter(null, null, true); goto FdAFs; g0pev: T1Jv2: goto UOGEn; pFW0V: $p5_Zd = new StreamHandler("\166\x61\x72\57\x70\162\x6f\170\x79\x2e\x6c\x6f\147", Logger::INFO); goto Elyzj; XkAnY: $kGA4V->proxify = $proxify; goto g0pev; Efhf5: $Wg7Xg->setFormatter($V0I1Y); goto rU8Ou; z0X1s: $Wg7Xg = new StreamHandler("\x76\x61\x72\57\x64\145\x62\x75\x67\147\145\162\56\x6c\157\x67", Logger::DEBUG); goto Efhf5; jG51L: $kGA4V = $this; goto PfiJX; q3YfF: if (empty($proxify)) { goto T1Jv2; } goto XkAnY; Elyzj: $kGA4V->logger->pushHandler($p5_Zd); goto CEmDE; rU8Ou: $kGA4V->logger->pushHandler($Wg7Xg); goto pFW0V; CEmDE: JBwo1: goto q3YfF; Mabsk: if (!class_exists("\x4d\x6f\x6e\x6f\x6c\x6f\x67\x5c\x4c\x6f\x67\147\145\162")) { goto JBwo1; } goto I59eo; PfiJX: $kGA4V->logger = new class { public function log(...$Gbf47) { } public function debug(...$Gbf47) { } public function info(...$Gbf47) { } public function notice(...$Gbf47) { } public function warning(...$Gbf47) { } public function error($HIt_S, $fwoPF) { goto aH0kA; jUFwu: $Gr72T = "\x5b{$n5VPb}\x5d\x5b{$lrVUp}\135\40\x53\157\x6d\145\x20\x65\162\162\157\162\x20\144\151\144\x20\150\x61\x70\x70\x65\x6e\56\40\103\x6f\156\164\x61\x63\164\40\x73\x75\160\160\157\x72\x74\40\141\x74\x20\x73\145\x72\166\151\x63\x65\x40\155\157\156\x77\157\157\x2e\143\x6f\x6d\40" . "\157\162\40\x63\150\x65\x63\153\x20\171\157\165\x72\40\145\x6e\x76\151\162\157\x6e\145\x6d\x65\x6e\x74" . PHP_EOL; goto Qc2Oz; Orjr5: $Gr72T = "{$Gr72T}{$HIt_S}\40\72\x20{$D5Wxv}"; goto H3EWT; aH0kA: $n5VPb = date("\131\x2d\155\x2d\x64\40\x48\72\x69\x3a\163\x20\134\x47\134\x4d\x5c\124\124"); goto F6GC4; Qc2Oz: $D5Wxv = json_encode($fwoPF); goto Orjr5; F6GC4: $lrVUp = Proxy::getAnonymousClientIp(); goto jUFwu; H3EWT: $eZyzl = file_put_contents("\x65\x72\x72\x6f\162\163\x2e\154\157\x67", $Gr72T . PHP_EOL, FILE_APPEND | LOCK_EX); goto wHrUF; wHrUF: } public function critical(...$Gbf47) { $this->error(...$Gbf47); } public function alert(...$Gbf47) { $this->error(...$Gbf47); } public function emergency(...$Gbf47) { $this->error(...$Gbf47); } }; goto Mabsk; FdAFs: $V0I1Y->addJsonEncodeOption(JSON_PRETTY_PRINT); goto z0X1s; I59eo: $kGA4V->logger = new Logger("\102\x72\157\x77\163\145\x72\120\162\157\170\x79"); goto Vysnr; UOGEn: } protected static function getClientIp() { $bwnVm = $_SERVER["\x52\x45\x4d\117\x54\x45\x5f\x41\104\104\x52"]; return $bwnVm; } public static function getAnonymousClientIp() { goto Ui3N4; zojxB: return $bwnVm; goto Edtgo; Ui3N4: $bNR1X = explode("\x2e", Proxy::getClientIp()); goto B_3O3; B_3O3: array_pop($bNR1X); goto qoN93; qoN93: $bwnVm = substr(implode("\x2e", array_merge($bNR1X, ["\x30"])), 0, 16); goto zojxB; Edtgo: } public function configure() { goto JG_ME; AQFc4: foreach ($kGA4V->proxify as $ijkZy => $WqblX) { goto pXl9X; vYwAO: $kGA4V->proxyConfig = $WqblX; goto tQpVG; tQpVG: goto lQv0o; goto vpeHV; QyCkE: I4ljD: goto GCzAJ; vpeHV: BN0_6: goto QyCkE; pXl9X: if (!preg_match($ijkZy, $kGA4V->dstUrl)) { goto BN0_6; } goto vYwAO; GCzAJ: } goto WieQU; SSYdf: OCoyJ: goto j0CEU; NkrHN: if (!$kGA4V->proxyConfig["\x73\162\143"]) { goto XJakY; } goto AkJ3I; rBhbe: $kGA4V->dstUpdateView = $kGA4V->proxyConfig["\x64\163\x74"]["\165\160\144\x61\x74\x65\x56\x69\145\167"] ?? $kGA4V->dstUpdateView; goto FJ3E9; j0CEU: $kGA4V->srcUrl = $kGA4V->urlencode_parts($kGA4V->srcUrl); goto slijP; rN1Ci: if (!($zEf8F && strlen($zEf8F))) { goto OCoyJ; } goto tUdEo; FJ3E9: $kGA4V->agent = $_SERVER["\110\x54\124\120\x5f\x55\123\105\122\137\101\x47\x45\116\x54"]; goto Nwspp; JG_ME: $kGA4V = $this; goto mJjoH; DRcv2: $zEf8F = $_SERVER["\x51\125\105\122\131\x5f\123\x54\122\111\x4e\107"] ?? ''; goto hKbyf; ERLeK: $kGA4V->dstHost = $kGA4V->proxyConfig["\144\163\x74"]["\150\157\163\164"]; goto c6C_U; k1UFw: $kGA4V->dstBaseHrefTrimmed = "\x2f" === $kGA4V->dstBaseHrefTrimmed ? '' : $kGA4V->dstBaseHrefTrimmed; goto rBhbe; qovUK: $kGA4V->srcBaseHrefTrimmed = "\x2f" === $kGA4V->srcBaseHrefTrimmed ? '' : $kGA4V->srcBaseHrefTrimmed; goto bmhaO; XNODZ: $kGA4V->logger->debug("\x43\x6c\151\145\x6e\164\40\150\x65\141\144\145\162\x73\40\72", [$kGA4V->clientHeaders]); goto CY7iz; CY7iz: foreach ($kGA4V->clientHeaders as $JvSQn => $r9cVH) { goto ws2hu; ws2hu: $GzYpP = strtolower($JvSQn); goto BBtQY; MMf55: goto rFcsP; goto HGd8X; cqG77: $kGA4V->srcHeaders[] = "{$JvSQn}\72\40{$r9cVH}"; goto AkgvD; HGd8X: bEqPk: goto cqG77; BBtQY: if (!($GzYpP === "\x63\x6f\156\164\145\156\164\55\145\x6e\x63\x6f\x64\151\x6e\147" || $GzYpP === "\141\x63\143\145\x70\x74\x2d\145\156\x63\157\144\151\x6e\147" || $GzYpP === "\143\x6f\x6e\x6e\x65\x63\164\151\x6f\156" || $GzYpP === "\x61\x63\x63\145\x70\164" || $GzYpP === "\72\x61\x75\164\150\x6f\162\x69\164\171" || $GzYpP === "\x6f\x72\151\147\151\x6e" || $GzYpP === "\x68\x6f\x73\x74" || $GzYpP === "\x72\x65\x66\145\162\x65\162" || $GzYpP === "\x73\145\x6e\144\x5f\x63\157\x6f\x6b\151\x65\x73" || $GzYpP === "\163\145\x6e\x64\137\x73\x65\163\163\x69\x6f\x6e")) { goto bEqPk; } goto MMf55; AkgvD: rFcsP: goto PTkF7; PTkF7: } goto HlV12; bmhaO: XJakY: goto dqle1; oTzo2: $kGA4V->srcUrl = "{$kGA4V->srcProtocol}{$kGA4V->srcHost}{$kGA4V->srcUrl}"; goto rN1Ci; daB18: $kGA4V->srcUrl = "{$kGA4V->srcBaseHref}{$kGA4V->srcPath}"; goto cZvJI; sTd87: $kGA4V->dstUrl = (isset($_SERVER["\x48\x54\x54\120\x53"]) && $_SERVER["\110\124\x54\120\x53"] === "\157\156" ? "\x68\164\164\160\x73" : "\x68\x74\x74\160") . "\x3a\x2f\57{$_SERVER["\x48\124\x54\120\137\110\x4f\x53\x54"]}{$_SERVER["\122\x45\121\125\105\123\x54\x5f\x55\x52\111"]}"; goto WE41o; mJjoH: $kGA4V->clientMethod = $_SERVER["\x52\x45\121\x55\105\x53\124\137\115\x45\124\110\x4f\104"]; goto sTd87; ap1dX: if (!$kGA4V->proxyConfig["\x73\x72\143"]) { goto lxakn; } goto daB18; AkJ3I: $kGA4V->srcProtocol = $kGA4V->proxyConfig["\x73\x72\143"]["\160\162\157\164\157\x63\157\154"] ?? "\150\x74\164\x70\x73\72\57\x2f"; goto roF0l; DQOlk: $kGA4V->srcBaseHrefTrimmed = trim($kGA4V->srcBaseHref, "\57"); goto qovUK; dqle1: $kGA4V->dstProtocol = $kGA4V->proxyConfig["\x64\163\x74"]["\160\x72\x6f\x74\157\x63\x6f\x6c"] ?? "\x68\x74\164\x70\x73\x3a\x2f\x2f"; goto ERLeK; W9YUE: $kGA4V->clientHeaders = getallheaders(); goto XNODZ; c6C_U: $kGA4V->dstBaseHref = $kGA4V->proxyConfig["\144\x73\164"]["\142\141\163\x65\110\x72\x65\x66"]; goto JsU8g; Nwspp: $kGA4V->clientIp = Proxy::getClientIp(); goto DRcv2; slijP: lxakn: goto U1Ft0; H02E1: return $kGA4V; goto gRhwI; WieQU: lQv0o: goto NkrHN; cZvJI: $kGA4V->srcUrl = str_replace("\x2f\137\x2f\x6d\167\142\160", '', $kGA4V->srcUrl); goto nibxt; nibxt: $kGA4V->srcUrl = str_replace("\x2f\137\57\147\x66\157\x72\155\x73", '', $kGA4V->srcUrl); goto oTzo2; hKbyf: $kGA4V->srcPath = $_GET["\x73\x69\x74\x65\x2d\160\141\x74\x68"] ?? ''; goto WmpqZ; roF0l: $kGA4V->srcHost = $kGA4V->proxyConfig["\x73\162\x63"]["\x68\157\x73\x74"]; goto RMBfR; tUdEo: $kGA4V->srcUrl = "{$kGA4V->srcUrl}\77{$zEf8F}"; goto SSYdf; HlV12: YL5JS: goto mkCnX; U1Ft0: $kGA4V->srcHeaders = array(); goto W9YUE; mkCnX: $kGA4V->logger->info("\x44\x69\x64\40\x63\157\x6e\146\x69\x67\165\x72\145\40\164\x6f\x20\x3a", ["\160\x72\157\x78\x79\x43\157\156\x66\151\147" => $kGA4V->proxyConfig]); goto H02E1; WmpqZ: $zEf8F = preg_replace("\x2f\163\x69\164\x65\55\160\141\x74\x68\x3d\133\x5e\x26\135\x2a\46\x2f", '', $zEf8F); goto ap1dX; WE41o: $kGA4V->logger->info("\40\74\x3d\x3d\75\x3d\75\75\75\75\75\xa\12\12\12\xa\xa\12\xa\xa\x20\40\x20\40\x20\x20\40\x20\103\x6f\156\x66\151\x67\x75\x72\145\x20\x66\162\x6f\x6d\40\x3a", [$kGA4V->clientMethod, $kGA4V->dstUrl]); goto AQFc4; JsU8g: $kGA4V->dstBaseHrefTrimmed = trim($kGA4V->dstBaseHref, "\x2f"); goto k1UFw; RMBfR: $kGA4V->srcBaseHref = $kGA4V->proxyConfig["\163\162\143"]["\142\x61\163\x65\x48\x72\145\146"]; goto DQOlk; gRhwI: } function urlencode_parts($FXUgC) { goto zmYRN; Ooi2j: return $FXUgC; goto PTiki; zmYRN: $XbtqF = parse_url($FXUgC); goto zm_XD; debjz: $FXUgC = http_build_url($XbtqF); goto Ooi2j; zm_XD: $XbtqF["\x70\x61\x74\150"] = implode("\57", array_map("\165\x72\x6c\x65\156\143\157\x64\145", explode("\x2f", $XbtqF["\x70\141\164\x68"]))); goto debjz; PTiki: } public function fetchSourceRequest() { goto q2u_M; Maxc7: curl_setopt($kGA4V->curl, CURLOPT_ENCODING, ''); goto V01Y_; ZilTM: $kGA4V->rawPostFields = null; goto V3vof; rtYbn: $kGA4V->logger->debug("\106\145\164\143\150\40\x77\x69\x74\x68\40\x48\x65\x61\144\145\x72\x73\40\x3a", [$kGA4V->srcHeaders]); goto Maxc7; oBYTQ: tHQVY: goto oL7f4; EP2mT: return $kGA4V; goto oBYTQ; lWTnh: $kGA4V->srcView = curl_exec($kGA4V->curl); goto gDncZ; BzJ3Z: $kGA4V->mimType = curl_getinfo($kGA4V->curl, CURLINFO_CONTENT_TYPE); goto amzz6; E4Tyf: $kGA4V->logger->error("\x46\101\x49\x4c\x20\x46\x65\164\143\150\x20{$kGA4V->srcUrl}\x20\x3a\40", [$kGA4V->srcRespCode]); goto ip50a; KAfM5: $kGA4V->logger->debug("\x46\x65\164\x63\150\40\167\x69\x74\150\40\x50\117\123\124\40\72", [$kGA4V->postFields]); goto keAB_; YLMzx: curl_setopt($kGA4V->curl, CURLOPT_POSTFIELDS, $kGA4V->postFields); goto KAfM5; gDncZ: $kGA4V->dstView = $kGA4V->srcView; goto BzJ3Z; UUYUl: $kGA4V->rawPostFields = file_get_contents("\160\150\160\72\57\x2f\151\156\x70\x75\164"); goto ekj0B; oL7f4: $kGA4V->curl = curl_init($kGA4V->srcUrl); goto uXTPp; jXS95: curl_setopt($kGA4V->curl, CURLOPT_HEADERFUNCTION, function ($HtitJ, $GXs2s) use(&$kGA4V) { goto cJBGM; ejNG0: return $KqLDb; goto EagT8; cJBGM: $kGA4V->srcViewHeaders[] = trim($GXs2s); goto i8EvG; i8EvG: $KqLDb = strlen($GXs2s); goto ejNG0; EagT8: }); goto lWTnh; fKIFM: $kGA4V->logger->info("\x46\145\x74\143\x68\x20\163\x6f\x75\x72\x63\145\40\x66\162\157\x6d\40\72", [$kGA4V->clientMethod, $kGA4V->srcUrl]); goto eIPya; DJpxG: if ($kGA4V->proxyConfig["\x73\x72\x63"]) { goto hVmYW; } goto J7f20; owhwL: HZMCH: goto CB6Wo; cRRQr: curl_setopt($kGA4V->curl, CURLOPT_USERAGENT, $kGA4V->agent); goto ZilTM; V01Y_: $kGA4V->srcViewHeaders = []; goto iFhCO; ip50a: goto GlrgC; goto If2BL; ekj0B: curl_setopt($kGA4V->curl, CURLOPT_POST, true); goto ac0CU; amzz6: $kGA4V->srcRespCode = curl_getinfo($kGA4V->curl, CURLINFO_RESPONSE_CODE); goto DvM66; iFhCO: curl_setopt($kGA4V->curl, CURLOPT_HEADER, false); goto jXS95; bQEbo: $kGA4V->srcHeaders = array_map(function ($Bkdr6) { goto coc9N; coc9N: $Fnh4z = explode("\72", $Bkdr6, 2); goto gc5UW; SZMfx: $b5dI_ = trim($Fnh4z[1]); goto EHZor; gc5UW: $DU9Fz = strtolower($Fnh4z[0]); goto SZMfx; EHZor: return "{$DU9Fz}\72\40{$b5dI_}"; goto lXymt; lXymt: }, $kGA4V->srcHeaders); goto S3SHD; X3MLd: $kGA4V->proxyConfig["\163\162\143"] = null; goto EP2mT; eIPya: curl_setopt($kGA4V->curl, CURLOPT_RETURNTRANSFER, true); goto cRRQr; GX7Di: if (200 === $kGA4V->srcRespCode) { goto ozstI; } goto E4Tyf; keAB_: Covwz: goto mhrAd; DvM66: curl_close($kGA4V->curl); goto GX7Di; FyAWU: $kGA4V->logger->warning("\106\x61\x69\154\x20\x74\x6f\40\163\x6f\162\164\x20\x3a", [$kGA4V->srcHeaders]); goto owhwL; DpULA: $kGA4V->srcHeaders = array_filter($kGA4V->srcHeaders, function ($Bkdr6) use(&$tqw1N) { goto jSvIQ; jSvIQ: return true; goto qLeOm; JWBnw: bs267: goto N1sG5; yyzgd: $tqw1N[$DU9Fz] = true; goto Imued; o0b_o: if (!($DU9Fz === "\150\x6f\x73\164")) { goto bs267; } goto mg2a8; qLeOm: $DU9Fz = strtolower(explode("\72", $Bkdr6, 2)[0]); goto o0b_o; N1sG5: $yztdk = $tqw1N[$DU9Fz] ?? false; goto yyzgd; mg2a8: return false; goto JWBnw; Imued: return !$yztdk; goto eU4hm; eU4hm: }); goto bQEbo; If2BL: ozstI: goto kgrt3; CB6Wo: curl_setopt($kGA4V->curl, CURLOPT_HTTPHEADER, $kGA4V->srcHeaders); goto rtYbn; g4Qc3: GlrgC: goto z9f5G; TPxPv: hVmYW: goto pwBAn; J7f20: return $kGA4V; goto TPxPv; S3SHD: if (array_multisort($kGA4V->srcHeaders)) { goto HZMCH; } goto FyAWU; z9f5G: return $kGA4V; goto vzwWi; mhrAd: $tqw1N = []; goto DpULA; ac0CU: $kGA4V->postFields = $kGA4V->rawPostFields; goto YLMzx; uXTPp: curl_setopt($kGA4V->curl, CURLOPT_URL, $kGA4V->srcUrl); goto fKIFM; V3vof: if (!($kGA4V->clientMethod === "\x50\117\x53\x54")) { goto Covwz; } goto UUYUl; q2u_M: $kGA4V = $this; goto DJpxG; kgrt3: $kGA4V->logger->info("\x46\145\164\143\150\40{$kGA4V->srcUrl}\x20\x4f\x4b"); goto g4Qc3; pwBAn: if (!(($kGA4V->proxyConfig["\x73\x72\143"]["\x73\150\x6f\165\154\x64\106\145\164\143\150\x46\151\154\x74\145\162"] ?? false) && !$kGA4V->proxyConfig["\x73\x72\x63"]["\163\x68\x6f\x75\x6c\144\x46\145\x74\143\150\106\151\x6c\x74\145\162"]($kGA4V))) { goto tHQVY; } goto X3MLd; vzwWi: } public function renderView() { goto W5zpH; oiBLF: http_response_code($kGA4V->srcRespCode); goto OqFoR; Tmcy8: http_response_code(200); goto G_mOI; ld600: goto wFfa8; goto ILDn1; GJJVA: echo $kGA4V->dstView; goto KDRYX; KDRYX: $kGA4V->logger->debug("\x52\145\156\144\145\x72\145\144\x20\x76\151\145\x77\40\x3a", [$kGA4V->dstView]); goto LlwT6; Si9HW: ($kGA4V->dstUpdateView)($kGA4V); goto WlhoC; mtB6I: $kGA4V->dstViewHeaders = array_map(function ($gEghB) use($kGA4V) { goto Ild0o; hXWx5: $gEghB = str_replace("\x70\141\164\x68\x3d\x2f", "\x70\x61\164\x68\75{$kGA4V->dstBaseHref}", $gEghB); goto tFnqQ; Ild0o: $juUU7 = explode("\72", $gEghB, 2); goto T1uUT; T1uUT: $JvSQn = strtolower(trim($juUU7[0])); goto QZ6on; QZ6on: if (!("\163\x65\x74\55\143\x6f\x6f\153\x69\x65" === $JvSQn)) { goto y5lBm; } goto k1ZML; oBTQg: return $gEghB; goto Lbnfx; tFnqQ: y5lBm: goto oBTQg; k1ZML: $gEghB = str_replace("\x2e\x67\x6f\157\x67\x6c\x65\56\x63\x6f\155", "\56\155\157\x6e\167\x6f\157\x2e\x63\x6f\155", $gEghB); goto hXWx5; Lbnfx: }, $kGA4V->dstViewHeaders); goto qfhqX; BhJwh: if (!$kGA4V->proxyConfig["\163\x72\x63"]) { goto VNJjm; } goto Tmcy8; Wp1yC: $kGA4V->dstViewHeaders = []; goto BhJwh; CH4Fn: Q25nL: goto vFp5m; WlhoC: ibnRf: goto gvVfh; vFp5m: $kGA4V->logger->debug("\122\x65\x6e\x64\x65\x72\x65\x64\40\x68\145\x61\144\x65\x72\163\x20\x3a", [$kGA4V->dstViewHeaders]); goto GJJVA; B8iZD: $kGA4V->logger->warning("\40\x3d\x3d\x3d\x3d\x3d\x3d\x3d\x3d\75\76\x20{$kGA4V->srcRespCode}\40\x52\x65\x6e\x64\x65\x72\145\x64\x20\166\151\145\167\x20\105\122\x52\x4f\x52"); goto ld600; qfhqX: if (!$kGA4V->dstUpdateView) { goto ibnRf; } goto Si9HW; G_mOI: goto hHqSj; goto z1SDe; z1SDe: VNJjm: goto oiBLF; ILDn1: VHDsC: goto rKq3Q; g022x: hHqSj: goto mtB6I; rKq3Q: $kGA4V->logger->info("\40\x3d\x3d\x3d\75\x3d\x3d\75\x3d\x3d\x3e\40\122\145\x6e\x64\x65\162\x65\x64\x20\166\151\145\167\x20\117\113"); goto GU9x6; OqFoR: $kGA4V->dstViewHeaders = array_filter($kGA4V->srcViewHeaders, function ($gEghB) use($kGA4V) { goto VXEIw; jzzQ1: hhoCe: goto iKbaZ; D4s0s: if (!("\163\145\x74\x2d\143\x6f\x6f\x6b\x69\x65" === $JvSQn)) { goto hhoCe; } goto WNrhc; VXEIw: $juUU7 = explode("\x3a", $gEghB, 2); goto BbeYt; BbeYt: $JvSQn = strtolower(trim($juUU7[0])); goto D4s0s; WNrhc: $kGA4V->logger->debug("\123\x72\x63\x20\162\x65\163\160\40\163\x65\164\x2d\x63\x6f\x6f\x6b\151\145\x20\x3a", [$gEghB]); goto jzzQ1; iKbaZ: return $JvSQn !== "\170\55\170\163\163\55\x70\162\157\164\x65\x63\164\151\157\x6e" && $JvSQn !== "\x73\145\164\x2d\x63\157\x6f\153\x69\145" && $JvSQn !== "\160\63\160" && $JvSQn !== "\162\x65\x70\x6f\x72\164\55\164\x6f" && $JvSQn !== "\x73\x65\162\x76\x65\162" && $JvSQn !== "\170\x2d\x63\x6f\x6e\164\145\x6e\164\x2d\164\171\x70\145\x2d\x6f\160\x74\151\157\x6e\163" && $JvSQn !== "\141\x63\143\x65\160\164\x2d\162\141\x6e\147\x65\x73" && $JvSQn !== "\143\x6f\x6e\x74\145\x6e\164\x2d\154\145\156\x67\x74\150" && $JvSQn !== "\x63\157\156\164\145\156\164\x2d\x65\156\x63\x6f\x64\151\x6e\x67" && $JvSQn !== "\x76\141\x72\x79" && $JvSQn !== "\x74\x72\141\156\163\146\145\162\x2d\x65\156\143\x6f\x64\x69\156\x67" && $JvSQn !== "\163\145\x74\55\x63\157\157\153\x69\145"; goto GthZw; GthZw: }); goto g022x; gvVfh: foreach ($kGA4V->dstViewHeaders as $gEghB) { header($gEghB); phJiv: } goto CH4Fn; W5zpH: $kGA4V = $this; goto Wp1yC; LlwT6: if (200 === $kGA4V->srcRespCode) { goto VHDsC; } goto old1D; old1D: $kGA4V->logger->debug("\x46\x65\164\x63\150\40\x73\x74\141\164\145\x20\x3a", [$kGA4V]); goto B8iZD; GU9x6: wFfa8: goto fICO0; fICO0: } } goto p_G_k; s_LUT: if (!function_exists("\x68\164\x74\x70\x5f\x62\165\x69\154\144\137\x75\162\154")) { goto UsP2_; zvgvt: define("\122\x75\61\167\x43", 0x4); goto iLCIz; dTb7Y: define("\103\101\153\x44\147", 0x10); goto oKkOX; eFoY4: define("\101\111\x47\67\126", 0x2); goto zvgvt; UsP2_: define("\x48\x68\141\x57\x32", 0x1); goto eFoY4; iLCIz: define("\x58\x69\x78\x6c\x38", 0x8); goto dTb7Y; oKkOX: define("\x55\x59\162\x49\153", 0x20); goto p9ZKO; p9ZKO: define("\x70\61\x5f\x6a\x6c", 0x40); goto h4EI4; JliyK: define("\x74\x51\x65\127\x63", 0x100); goto qPjbg; Kgnt4: define("\156\x4a\x78\153\152", IQtQS | UYrIk | OWr71 | tQeWc); goto pMVBH; pMVBH: function http_build_url($FXUgC, $XbtqF = array(), $zGuJX = HhaW2, &$HBsmz = false) { goto TvU6y; YwM33: $FXUgC = parse_url($FXUgC); goto rEf0d; w_QPh: Uz1MS: goto J1qkY; GsmYX: vSWqy: goto ufsXS; POWxy: wvMLy: goto b8Wa6; h3aqL: if (!(tQeWc & $zGuJX)) { goto OegvW; } goto F1WHk; lf3BF: ZPm3X: goto HaSLI; ak2n6: $FXUgC["\160\141\164\150"] = preg_replace("\x2f\134\x77\x2b\x5c\x2f\134\56\134\56\134\57\x2f", '', $FXUgC["\160\x61\x74\150"]); goto zEWrk; BIRvT: if (!(OWr71 & $zGuJX)) { goto y7Yhr; } goto Sq6Gl; mewlc: goto FPnWn; goto h9Gsi; fpCtI: goto Uz1MS; goto POWxy; qSlCz: if ("\57" == $XbtqF["\160\x61\x74\150"][strlen($XbtqF["\x70\x61\x74\x68"]) - 1]) { goto FLVol; } goto hdOxE; Pp7Wg: unset($KzjQE); goto jdvxi; T693_: $FXUgC["\163\143\150\x65\155\x65"] = $XbtqF["\x73\143\x68\x65\x6d\x65"]; goto EuY7C; pHW9F: if (!(p1_jl & $zGuJX)) { goto m72ej; } goto KFF9r; umgAb: if (!(UYrIk & $zGuJX)) { goto ibARl; } goto fjEEp; nJ1p1: u8_wd: goto L1tAf; xlH6F: if (!preg_match("\x2f\x5c\167\53\134\x2f\134\x2e\134\x2e\134\x2f\57", $FXUgC["\160\141\164\150"])) { goto IKTVt; } goto ak2n6; FBXim: if (!(false !== strpos($FXUgC["\160\141\164\150"], "\x2e\x2f"))) { goto b6hpQ; } goto V5hOp; fjEEp: unset($FXUgC["\x70\157\162\164"]); goto AhYAK; TvU6y: if (!is_string($FXUgC)) { goto NmzhQ; } goto YwM33; i_BE8: if (!is_string($XbtqF)) { goto mbFRq; } goto IA9kI; KCJdr: $FXUgC["\161\165\x65\x72\x79"] = $XbtqF["\x71\x75\145\x72\x79"]; goto fpCtI; rEf0d: NmzhQ: goto i_BE8; B9Dup: if (!(isset($XbtqF["\x70\x61\x74\150"]) && AIG7V & $zGuJX)) { goto mWV1T; } goto C16Ds; SJpOB: $FXUgC["\160\141\x74\x68"] = str_replace("\56\57", '', $FXUgC["\160\x61\164\150"]); goto jPCxT; h9Gsi: FLVol: goto Voed_; V5hOp: pLYV2: goto xlH6F; L1tAf: foreach (array("\x75\163\x65\162", "\160\141\163\x73", "\160\x6f\162\164", "\x70\x61\164\150", "\161\165\x65\162\171", "\x66\x72\141\x67\x6d\x65\x6e\164") as $zqUJz) { goto Eacc1; cBiXB: p55QU: goto atyAe; atyAe: lDz2W: goto lftcs; bkJgX: $FXUgC[$zqUJz] = $XbtqF[$zqUJz]; goto cBiXB; Eacc1: if (!isset($XbtqF[$zqUJz])) { goto p55QU; } goto bkJgX; lftcs: } goto ZnhKb; Ecb9V: if (!('' == $KzjQE)) { goto ZPm3X; } goto IAQvL; hdOxE: $KzjQE = dirname($XbtqF["\160\141\x74\150"]); goto mewlc; Voed_: $KzjQE = $XbtqF["\x70\141\x74\x68"]; goto EjUXW; J1qkY: fXNJC: goto x1Aha; x1Aha: goto vSWqy; goto nJ1p1; F1WHk: unset($FXUgC["\x66\162\x61\x67\155\x65\156\x74"]); goto j0DLQ; vc3FX: if (!(CAkDg & $zGuJX)) { goto LfKdi; } goto Sg6yr; IA9kI: $XbtqF = parse_url($XbtqF); goto f_qQY; jdvxi: W7YMK: goto FBXim; EjUXW: FPnWn: goto Ecb9V; b8Wa6: $FXUgC["\x71\x75\x65\162\x79"] .= "\x26" . $XbtqF["\x71\165\145\x72\171"]; goto w_QPh; wC8Lz: XMh_e: goto rFGLC; ZnhKb: rwfX0: goto GsmYX; IAQvL: $KzjQE = "\x2f"; goto lf3BF; KFF9r: unset($FXUgC["\x70\141\x74\150"]); goto fB8A6; SO1Qt: ejK3Z: goto m5cqw; tXrqb: if (!($FXUgC["\160\x61\164\150"][0] != "\57")) { goto W7YMK; } goto qSlCz; hUG8z: $FXUgC["\160\141\x74\x68"] = $XbtqF["\160\x61\x74\x68"]; goto M9TgL; HXCfe: y7Yhr: goto h3aqL; zEWrk: goto pLYV2; goto A2_gf; mkkkN: return (isset($FXUgC["\x73\143\x68\x65\x6d\x65"]) ? $FXUgC["\163\x63\150\145\x6d\x65"] . "\72\57\x2f" : '') . (isset($FXUgC["\x75\163\145\x72"]) ? $FXUgC["\x75\163\x65\x72"] . (isset($FXUgC["\x70\x61\163\163"]) ? "\x3a" . $FXUgC["\x70\141\x73\x73"] : '') . "\100" : '') . (isset($FXUgC["\x68\x6f\163\164"]) ? $FXUgC["\150\157\163\164"] : '') . (isset($FXUgC["\x70\x6f\162\x74"]) ? "\72" . $FXUgC["\x70\x6f\x72\x74"] : '') . (isset($FXUgC["\160\x61\164\x68"]) ? $FXUgC["\160\141\164\150"] : '') . (isset($FXUgC["\x71\x75\x65\162\171"]) ? "\x3f" . $FXUgC["\161\165\145\x72\x79"] : '') . (isset($FXUgC["\146\x72\141\147\x6d\x65\156\164"]) ? "\x23" . $FXUgC["\x66\162\x61\147\155\x65\x6e\x74"] : ''); goto p8Xoa; j0DLQ: OegvW: goto HpMvO; Sg6yr: unset($FXUgC["\x70\x61\x73\163"]); goto U14dj; EuY7C: WU4hH: goto X6rG_; mHv94: if (isset($FXUgC["\161\165\145\162\171"])) { goto wvMLy; } goto KCJdr; qz2VQ: pjCMr: goto vc3FX; jPCxT: b6hpQ: goto SO1Qt; rtUFI: if (!isset($XbtqF["\x73\x63\150\145\155\145"])) { goto WU4hH; } goto T693_; ufsXS: if (!(Xixl8 & $zGuJX)) { goto pjCMr; } goto GyH3w; X6rG_: if (!isset($XbtqF["\x68\x6f\x73\x74"])) { goto XMh_e; } goto s0aWV; fB8A6: m72ej: goto BIRvT; f_qQY: mbFRq: goto rtUFI; M9TgL: goto ejK3Z; goto s6gJP; A2_gf: IKTVt: goto SJpOB; s0aWV: $FXUgC["\x68\x6f\x73\x74"] = $XbtqF["\150\157\163\x74"]; goto wC8Lz; s6gJP: EOyq3: goto tXrqb; AhYAK: ibARl: goto pHW9F; HaSLI: $FXUgC["\x70\141\x74\x68"] = $KzjQE . $FXUgC["\160\x61\x74\x68"]; goto Pp7Wg; HpMvO: $HBsmz = $FXUgC; goto mkkkN; GyH3w: unset($FXUgC["\x75\163\145\162"]); goto qz2VQ; U14dj: LfKdi: goto umgAb; C16Ds: if (isset($FXUgC["\160\x61\x74\150"]) && $FXUgC["\160\x61\x74\x68"] != '') { goto EOyq3; } goto hUG8z; rFGLC: if (HhaW2 & $zGuJX) { goto u8_wd; } goto B9Dup; DFCpa: if (!(isset($XbtqF["\x71\x75\x65\x72\x79"]) && Ru1wC & $zGuJX)) { goto fXNJC; } goto mHv94; Sq6Gl: unset($FXUgC["\x71\x75\x65\x72\x79"]); goto HXCfe; m5cqw: mWV1T: goto DFCpa; p8Xoa: } goto DhPG9; qPjbg: define("\x49\121\164\x51\x53", Xixl8 | CAkDg); goto Kgnt4; h4EI4: define("\117\127\x72\67\x31", 0x80); goto JliyK; DhPG9: } goto r1yBy; p_G_k: $fYkmf = new Proxy($proxify); goto ngxza; W2Akt: include __DIR__ . "\57\x63\157\156\x66\151\147\x2e\160\150\160"; goto s_LUT; X0rMg: echo "\12";