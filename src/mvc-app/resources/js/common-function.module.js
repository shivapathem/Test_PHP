/**
 * Common function JavaScript
 *
 * @param {Object} p parameters
 */
var CommonFunction = function (p) {
};

CommonFunction.prototype = {
    /**
     * Datable accessbility text update
     */
    datatableAccessbilityText: function (tableObj) {
        setTimeout(function () {
            $('.dt-paging-button').each(function (index, element) {
                switch ($(element).text()) {
                    case '«':
                        $(element).attr('aria-label', 'First Page');
                        break;
                    case '‹':
                        $(element).attr('aria-label', 'Previous Page');
                        break;
                    case '›':
                        $(element).attr('aria-label', 'Next Page');
                        break;
                    case '»':
                        $(element).attr('aria-label', 'Last Page');
                        break;
                    default:
                        let pageText = 'Page ' + $(element).text();
                        if ($(element).hasClass('current')) {
                            pageText = pageText + ' Current Page'
                        }
                        $(element).removeAttr('aria-current');
                        $(element).attr('aria-label', pageText);
                        break;
                }
            });
        }, 100);
    },
    /**
     * Select2 custome matcher to search from first
     */
    select2CustomMatch: function (params, data) {
        const term = (params.term || "").toString().trim().toLowerCase();
        if (!term) return data; // show all when no query

        const text = (data.text || "").toString().toLowerCase();
        if (text.startsWith(term)) {
            return data;
        }
        return null; // filter out
    }
}

module.exports = CommonFunction;