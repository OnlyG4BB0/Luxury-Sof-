(function () {
  window.LUXURY_STORE = {
    CART_KEY: 'luxury_cart',
    WISH_KEY: 'luxury_wishlist',

    getCart: function () {
      try { return JSON.parse(localStorage.getItem(this.CART_KEY) || '[]'); } catch (e) { return []; }
    },
    saveCart: function (cart) {
      localStorage.setItem(this.CART_KEY, JSON.stringify(cart));
    },
    getWishlist: function () {
      try { return JSON.parse(localStorage.getItem(this.WISH_KEY) || '[]'); } catch (e) { return []; }
    },
    saveWishlist: function (list) {
      localStorage.setItem(this.WISH_KEY, JSON.stringify(list));
    },
    isInWishlist: function (sku) {
      return this.getWishlist().some(function (x) { return String(x.sku) === String(sku); });
    },
    toggleWishlist: function (item) {
      var w = this.getWishlist();
      var idx = w.findIndex(function (x) { return String(x.sku) === String(item.sku); });
      if (idx >= 0) {
        w.splice(idx, 1);
        this.saveWishlist(w);
        return false;
      }
      w.push(item);
      this.saveWishlist(w);
      return true;
    },
    updateWishlistBadges: function () {
      var n = this.getWishlist().length;
      document.querySelectorAll('.wishlist-badge').forEach(function (el) {
        el.textContent = n;
        el.style.display = n ? 'flex' : 'none';
      });
    }
  };
})();
