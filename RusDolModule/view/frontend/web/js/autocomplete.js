define(['uiComponent', 'jquery', 'mage/url', 'ko'], function (Component, $, urlBuilder, ko) {
    return Component.extend({
        defaults: {
            searchText: '',
            searchResult: null,
            searchLength: 3,
            showMessage: false
        },
        initObservable: function () {
            this._super();
            this.observe(['searchText', 'searchResult', 'showMessage']);
            return this;
        },
        initialize: function () {
            this._super();
            this.searchText.subscribe(this.handleAutocomplete.bind(this));
        },
        selectProduct: function (productObject) {
            this.searchText(productObject.sku);
            this.searchResult(null);
        },
        handleAutocomplete: function (searchText) {
            if (searchText.length < this.searchLength) {
                this.searchResult(null);
                this.showMessage(false);
            } else {
                $.ajax({
                    showLoader: true,
                    url: urlBuilder.build('ruslan/cart/autocomplete?sku=' + searchText),
                    type: "GET",
                    dataType: 'json',
                    statusCode: {
                        200: function (data) {
                            this.searchResult(data);
                            this.showMessage(false);
                        }.bind(this),
                        404: function () {
                            this.searchResult(null);
                            this.showMessage(true);
                        }.bind(this)
                    }
                });
            }
        }
    });
});