/* global Waypoint */
window.vcv.on('ready', function (action, id) {
  if (action !== 'merge') {
    vceAnimate.enableAnimate(action && id ? id : '');
  }
});

var vceAnimate = {
  enableAnimate: function enableAnimate(id) {
    window.Waypoint.destroyAll();
    var waypoints = [];
    var selector = id ? '[data-vcv-element="' + id + '"]' : '[data-vce-animate]';
    var elements = document.querySelectorAll(selector);
    elements = [].slice.call(elements);
    elements.forEach(function (element) {
      var _element$classList;
      if (id && !element.getAttribute('data-vce-animate')) {
        element = element.querySelector('[data-vce-animate]');
        if (!element) {
          return;
        }
      }
      // remove old classes
      var oldClasses = [];
      var re = /^vce-o-animate--/;
      element.classList.forEach(function (className) {
        if (className.search(re) !== -1) {
          oldClasses.push(className);
        }
      });
      (_element$classList = element.classList).remove.apply(_element$classList, oldClasses);
      var waypointObj = new window.Waypoint({
        element: element,
        handler: function handler() {
          var _this = this;

          setTimeout(function () {
            // add new classes
            var newClasses = [];
            if (_this.element.dataset['vceAnimate']) {
              newClasses = _this.element.dataset['vceAnimate'].split(' ');
            }
            newClasses.push('vce-o-animate--animated');
            newClasses.forEach(function (className) {
              _this.element.classList.add(className);
            });
            _this.destroy();
          }, 100);
        },
        offset: '80%'
      });
      waypoints.push(waypointObj);
    });
  }
};
