/****** Counter Handler ******/
var MEAFECounterHandler = function ($scope, $) {
    var counterElement = $scope.find(".meafe-counter");

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var counterSettings = counterElement.data(),
                    incrementElement = counterElement.find(".meafe-counter-init"),
                    iconElement = counterElement.find(".icon");
                $(incrementElement).numerator(counterSettings);
                $(iconElement).addClass("animated " + iconElement.data("animation"));
                observer.unobserve(counterElement[0]); // Stop observing after the animation is triggered
            }
        });
    });

    observer.observe(counterElement[0]);
};

jQuery(window).on("elementor/frontend/init", function() {
    elementorFrontend.hooks.addAction(
        "frontend/element_ready/meafe-counter.default",
        MEAFECounterHandler
    );
});