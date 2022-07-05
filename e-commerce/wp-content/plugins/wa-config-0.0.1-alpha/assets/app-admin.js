/*
* 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
* service@monwoo.com
*/
var jQuery;

(function(window, document, $, undefined){
    var iid = $('#wa_e_review_settings_form_key_wa_instance_iid').val();
    function suggestFactory(selector, query, defaultOpts = {}) {
        // TODO unit speed test bench with ? : https://code.tutsplus.com/tutorials/enhancing-the-search-form-with-typeaheadjs--wp-30844
        var target = $(selector);
        return target.length ? target.suggest(window.ajaxurl
        + query + "&wa-iid=" + iid, {
            // https://stackoverflow.com/questions/30128805/is-is-possible-to-override-a-suggest-js-function-in-wordpress
            delay: 500, minchars: 1,
            // onSelect: function() { do_something(this.value);},
            ...defaultOpts
        }) : null;
    }

    var waConfigAdmin = {
        init: function() {
            // suggestFactory(
            //     '.wa-suggest-blog-category',
            //     "?action=ajax-tag-search&tax=category",
            //     {multiple:true, multipleSep: ","}
            // );
            suggestFactory(
                '.wa-suggest-capabilities-and-roles',
                "?action=wa-list-capabilities-and-roles",
                {multiple:false}
            );
            suggestFactory(
                '.wa-suggest-list-review-data-by-category',
                "?action=wa-list-review-data-by-key&key=category",
                {multiple:false}
            );
            suggestFactory(
                '.wa-suggest-list-review-data-by-category_icon',
                "?action=wa-list-review-data-by-key&key=category_icon",
                {multiple:false}
            );
            suggestFactory(
                '.wa-suggest-list-review-data-by-title',
                "?action=wa-list-review-data-by-key&key=title",
                {multiple:false}
            );
            suggestFactory(
                '.wa-suggest-list-review-data-by-title_icon',
                "?action=wa-list-review-data-by-key&key=title_icon",
                {multiple:false}
            );
            suggestFactory(
                '.wa-review-textarea-wa_e_review_settings_form_key_wa_review_requirements',
                "?action=wa-list-review-data-by-key&key=requirements",
                {multiple:false}
            );
            suggestFactory(
                '.wa-suggest-list-review-data-by-value',
                "?action=wa-list-review-data-by-key&key=value",
                {multiple:false}
            );
            suggestFactory(
                '.wa-suggest-list-api-frontheads',
                "?action=wa-suggest-frontheads",
                {multiple:false}
            );
            
            // https://stackoverflow.com/questions/16286936/event-listener-for-multiple-elements-jquery
            $('.wa-checkbox').each((idx, target) => {
                target.onchange = (e) => {
                    e.target.value = '' + new Number(e.target.checked);
                    // if (!e.target.checked) {
                    //     // e.target.removeAttribute('checked'); // No effect, prop still here
                    //     e.target.prop('checked', false);
                    // }
                };
            });
            
            // Hide hidden fields
            $('[name^=wa_e_review][type=hidden], '
            + '[name^=wa_e_config][type=hidden]')
            .parents('tr').hide();
        }
    };
    
    waConfigAdmin.init();
    
})(window, document, jQuery);