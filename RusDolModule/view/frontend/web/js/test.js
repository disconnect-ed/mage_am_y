define(['jquery', 'mage/url'], function ($, urlBuilder) {
    $.widget('mynamespace.rusAutoComplete', {
        options: {
            minChars: null,
            searchUrl: urlBuilder.build('/search/ajax/suggest'),
            searchResultList: null,
            availableSku: [
                '2444-MB',
                '2444-MH',
                '123-OK'
            ]
        },
        _create: function () {
            this.options.searchResultList = $(this.element).find('.search-result-list');
            $(this.element).find('#search-inp').on('keyup', this.processAutocomplete.bind(this));
        },
        processAutocomplete: function (event) {
            var queryText = $(event.target).val();
            this.options.searchResultList.empty();
            if (queryText.length >= this.options.minChars) {
                $.getJSON(this.options.searchUrl, {
                        q: queryText
                    }, function (data) {
                        if (data.length) {
                            var searchList = data.map(function (item) {
                                return $('<li/>').text(item.title);
                            });
                            this.options.searchResultList.append(searchList);
                        } else {
                            this.options.searchResultList.empty();
                        }
                    }.bind(this));



                // var filteredSku = this.options.availableSku.filter(function (item) {
                //     return item.indexOf(queryText) != -1;
                // });
                // if (filteredSku.length) {
                //     var searchList = filteredSku.map(function (item) {
                //         return $('<li/>').text(item);
                //     });
                //     this.options.searchResultList.append(searchList);
                // } else {
                //     this.options.searchResultList.empty();
                // }
            }
        }
    });
    return $.mynamespace.rusAutoComplete;
})