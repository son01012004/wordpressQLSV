/*!
 * Layzr.js 2.0.4 - A small, fast, and modern library for lazy loading images.
 * Copyright (c) 2016 Michael Cavalea - https://callmecavs.github.io/layzr.js/
 * License: GPL-3.0
 */
! function(e) {
    if ("object" == typeof exports && "undefined" != typeof module) module.exports = e();
    else if ("function" == typeof define && define.amd) define([], e);
    else {
        var t;
        t = "undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this, t.Layzr = e()
    }
}(function() {
    var e;
    return function t(e, n, r) {
        function o(f, u) {
            if (!n[f]) {
                if (!e[f]) {
                    var s = "function" == typeof require && require;
                    if (!u && s) return s(f, !0);
                    if (i) return i(f, !0);
                    var c = new Error("Cannot find module '" + f + "'");
                    throw c.code = "MODULE_NOT_FOUND", c
                }
                var d = n[f] = {
                    exports: {}
                };
                e[f][0].call(d.exports, function(t) {
                    var n = e[f][1][t];
                    return o(n ? n : t)
                }, d, d.exports, t, e, n, r)
            }
            return n[f].exports
        }
        for (var i = "function" == typeof require && require, f = 0; f < r.length; f++) o(r[f]);
        return o
    }({
        1: [function(t, n, r) {
            (function(o) {
                ! function(t) {
                    if ("object" == typeof r && "undefined" != typeof n) n.exports = t();
                    else if ("function" == typeof e && e.amd) e([], t);
                    else {
                        var i;
                        i = "undefined" != typeof window ? window : "undefined" != typeof o ? o : "undefined" != typeof self ? self : this, i.Knot = t()
                    }
                }(function() {
                    return function e(n, r, o) {
                        function i(u, s) {
                            if (!r[u]) {
                                if (!n[u]) {
                                    var c = "function" == typeof t && t;
                                    if (!s && c) return c(u, !0);
                                    if (f) return f(u, !0);
                                    var d = new Error("Cannot find module '" + u + "'");
                                    throw d.code = "MODULE_NOT_FOUND", d
                                }
                                var a = r[u] = {
                                    exports: {}
                                };
                                n[u][0].call(a.exports, function(e) {
                                    var t = n[u][1][e];
                                    return i(t ? t : e)
                                }, a, a.exports, e, n, r, o)
                            }
                            return r[u].exports
                        }
                        for (var f = "function" == typeof t && t, u = 0; u < o.length; u++) i(o[u]);
                        return i
                    }({
                        1: [function(e, t, n) {
                            "use strict";
                            Object.defineProperty(n, "__esModule", {
                                value: !0
                            }), n["default"] = function() {
                                var e = arguments.length <= 0 || void 0 === arguments[0] ? {} : arguments[0];
                                return e.events = {}, e.on = function(t, n) {
                                    return e.events[t] = e.events[t] || [], e.events[t].push(n), e
                                }, e.once = function(t, n) {
                                    return n._once = !0, e.on(t, n), e
                                }, e.off = function(t, n) {
                                    return 2 === arguments.length ? e.events[t].splice(e.events[t].indexOf(n), 1) : delete e.events[t], e
                                }, e.emit = function(t) {
                                    for (var n = arguments.length, r = Array(n > 1 ? n - 1 : 0), o = 1; n > o; o++) r[o - 1] = arguments[o];
                                    var i = e.events[t] && e.events[t].slice();
                                    return i && i.forEach(function(n) {
                                        n._once && e.off(t, n), n.apply(e, r)
                                    }), e
                                }, e
                            }, t.exports = n["default"]
                        }, {}]
                    }, {}, [1])(1)
                })
            }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
        }, {}],
        2: [function(e, t, n) {
            "use strict";

            function r(e) {
                return e && e.__esModule ? e : {
                    "default": e
                }
            }
            Object.defineProperty(n, "__esModule", {
                value: !0
            });
            var o = e("knot.js"),
                i = r(o);
            n["default"] = function() {
                function e() {
                    return window.scrollY || window.pageYOffset
                }

                function t() {
                    a = e(), n()
                }

                function n() {
                    l || (requestAnimationFrame(function() {
                        return s()
                    }), l = !0)
                }

                function r(e) {
                    return e.getBoundingClientRect().top + a
                }

                function o(e) {
                    var t = a,
                        n = t + v,
                        o = r(e),
                        i = o + e.offsetHeight,
                        f = h.threshold / 100 * v;
                    return i >= t - f && n + f >= o
                }

                function f(e) {
                    if (m.emit("src:before", e), w && e.hasAttribute(h.srcset)) e.setAttribute("srcset", e.getAttribute(h.srcset));
                    else {
                        var t = y > 1 && e.getAttribute(h.retina);
                        e.setAttribute("src", t || e.getAttribute(h.normal))
                    }
                    m.emit("src:after", e), [h.normal, h.retina, h.srcset].forEach(function(t) {
                        return e.removeAttribute(t)
                    }), c()
                }

                function u(e) {
                    var n = e ? "addEventListener" : "removeEventListener";
                    return ["scroll", "resize"].forEach(function(e) {
                        return window[n](e, t)
                    }), this
                }

                function s() {
                    return v = window.innerHeight, p.forEach(function(e) {
                        return o(e) && f(e)
                    }), l = !1, this
                }

                function c() {
                    return p = Array.prototype.slice.call(document.querySelectorAll("[" + h.normal + "]")), this
                }
                var d = arguments.length <= 0 || void 0 === arguments[0] ? {} : arguments[0],
                    a = e(),
                    l = void 0,
                    p = void 0,
                    v = void 0,
                    h = {
                        normal: d.normal || "data-normal",
                        retina: d.retina || "data-retina",
                        srcset: d.srcset || "data-srcset",
                        threshold: d.threshold || 0
                    },
                    w = document.body.classList.contains("srcset") || "srcset" in document.createElement("img"),
                    y = window.devicePixelRatio || window.screen.deviceXDPI / window.screen.logicalXDPI,
                    m = (0, i["default"])({
                        handlers: u,
                        check: s,
                        update: c
                    });
                return m
            }, t.exports = n["default"]
        }, {
            "knot.js": 1
        }]
    }, {}, [2])(2)
});

var layzrInstance = Layzr({
    normal: 'data-layzr',
    srcset: 'data-layzr-srcset'
});

document.addEventListener('DOMContentLoaded', function() {
    layzrInstance.update().check().handlers(true);
});