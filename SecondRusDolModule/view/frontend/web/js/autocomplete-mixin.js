define(['uiComponent', 'jquery', 'mage/url', 'ko'], function (Component, $, urlBuilder, ko) {
    var mixin = {
        handleAutocomplete: function(searchText) {
            this.searchLength = 5;
            this._super();
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});