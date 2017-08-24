/**
 * Rating Manager Lite
 *
 * It is based on a script by Prashanth Pamidi.
 * rateYo
 * http://prrashi.github.io/rateyo/
 * Copyright (c) 2014 Prashanth Pamidi; Licensed MIT
 * 
 */
(function($) {
    "use strict";

    var DEFAULT_SETTINGS = {
        svgWidth: "24px",
        normalFill: "#d4d195",
        ratedFill: "#fdff00",
        numIcons: 5,
        minValue: 0,
        maxValue: 5,
        precision: 0,
        rating: 0,
        readOnly: false,
        onChange: null,
        onSet: null
    };

    function checkPercision(value, minValue, maxValue) {

        /* its like comparing 0.00 with 0 which is true*/
        if (value === minValue) {

            value = minValue;
        } else if (value === maxValue) {

            value = maxValue;
        }

        return value;
    }

    function checkBounds(value, minValue, maxValue) {

        var isValid = value >= minValue && value <= maxValue;

        if (!isValid) {

            throw Error("Invalid Rating, expected value between " + minValue +
                " and " + maxValue);
        }

        return value;
    }

    function getInstance(node, collection) {

        var instance;

        $.each(collection, function() {

            if (node === this.node) {

                instance = this;
                return false;
            }
        });

        return instance;
    }

    function deleteInstance(node, collection) {

        $.each(collection, function(index) {

            if (node === this.node) {

                var firstPart = collection.slice(0, index),
                    secondPart = collection.slice(index + 1, collection.length);

                collection = firstPart.concat(secondPart);

                return false;
            }
        });

        return collection;
    }

    function isDefined(value) {

        return typeof value !== "undefined";
    }

    /* The Contructor, whose instances are used by plugin itself,
     * to set and get values
     */
    function UR_Constructor($node, options) {

		var elm_rml_svg_source = options.svg_source;

        this.$node = $node;

        this.node = $node.get(0);

        var that = this;

        var rating = $(this.node).attr('data-elm-value');
        var read_only = $(this.node).attr('data-elm-readonly');

        if (typeof read_only !== 'undefined' && read_only == 'true') {
            options['readOnly'] = true;
        }

        if (typeof rating !== 'undefined') {
            options['rating'] = rating;
        }

        $node.addClass("jq-ry-container");

        var $groupWrapper = $("<div/>").addClass("jq-ry-group-wrapper")
            .appendTo($node);

        var $normalGroup = $("<div/>").addClass("jq-ry-normal-group")
            .addClass("jq-ry-group")
            .appendTo($groupWrapper);

        var $ratedGroup = $("<div/>").addClass("jq-ry-rated-group")
            .addClass("jq-ry-group")
            .appendTo($groupWrapper);

        function showRating(ratingVal) {

            if (!isDefined(ratingVal)) {

                ratingVal = options.rating;
            }

            var minValue = options.minValue,
                maxValue = options.maxValue;

            var percent = ((ratingVal - minValue) / (maxValue - minValue)) * 100;

            $ratedGroup.css("width", percent + "%");
        }

        function setStarWidth(newWidth) {

            if (!isDefined(newWidth)) {

                return options.svgWidth;
            }

            // In the current version, the width and height of the star
            // should be the same
            options.svgWidth = options.starHeight = newWidth;

            var containerWidth = parseInt(options.svgWidth.replace("px", "").trim());

            containerWidth = containerWidth * options.numIcons;

            $node.css({'min-width': containerWidth + 'px' });

            $normalGroup.find("svg")
                .attr({
                    width: options.svgWidth,
                    height: options.starHeight
                });

            $ratedGroup.find("svg")
                .attr({
                    width: options.svgWidth,
                    height: options.starHeight
                });
        }

        function setNormalFill(newFill) {

            if (!isDefined(newFill)) {

                return options.normalFill;
            }

            options.normalFill = newFill;

            $normalGroup.find("svg").attr({
                fill: options.normalFill
            });
        }

        function setRatedFill(newFill) {

            if (!isDefined(newFill)) {

                return options.ratedFill;
            }

            options.ratedFill = newFill;

            $ratedGroup.find("svg").attr({
                fill: options.ratedFill
            });
        }

        function setNumStars(newValue) {

            if (!isDefined(newValue)) {

                return options.numIcons;
            }

            options.numIcons = newValue;

            $normalGroup.empty();
            $ratedGroup.empty();

            for (var i = 0; i < options.numIcons; i++) {

                $normalGroup.append($(elm_rml_svg_source));
                $ratedGroup.append($(elm_rml_svg_source));
            }

            setStarWidth(options.svgWidth);
            setRatedFill(options.ratedFill);
            setNormalFill(options.normalFill);

            showRating();
        }

        function setMinValue(newValue) {

            if (!isDefined(newValue)) {

                return options.minValue;
            }

            options.minValue = newValue;

            showRating();

            return newValue;
        }

        function setMaxValue(newValue) {

            if (!isDefined(newValue)) {

                return options.maxValue;
            }

            options.maxValue = newValue;

            showRating();

            return newValue;
        }

        function setPrecision(newValue) {

            if (!isDefined(newValue)) {

                return options.precision;
            }

            options.precision = newValue;

            showRating();
        }

        function calculateRating(e) {

            var position = $normalGroup.offset(),
                nodeStartX = position.left,
                nodeEndX = nodeStartX + $normalGroup.width();

            var minValue = options.minValue,
                maxValue = options.maxValue;

            var pageX = e.pageX;

            var calculatedRating;

            if (pageX < nodeStartX) {

                calculatedRating = minValue;
            } else if (pageX > nodeEndX) {

                calculatedRating = maxValue;
            } else {

                calculatedRating = ((pageX - nodeStartX) / (nodeEndX - nodeStartX));
                calculatedRating *= (maxValue - minValue);
                calculatedRating += minValue;
            }

            var no_precision = 0.5;

            return calculatedRating + no_precision;
        }

        function onMouseEnter(e) {

            var rating = calculateRating(e).toFixed(options.precision);

            var minValue = options.minValue,
                maxValue = options.maxValue;

            rating = checkPercision(parseFloat(rating), minValue, maxValue);

            showRating(rating);

            $node.trigger("ultimateratings.change", {
                rating: rating
            });
        }

        function onMouseLeave() {

            showRating(rating);

            $node.trigger("ultimateratings.change", {
                rating: options.rating
            });
        }

        function onMouseClick(e) {

            var resultantRating = calculateRating(e).toFixed(options.precision);
            resultantRating = parseFloat(resultantRating);

            that.rating(resultantRating);
        }

        function onChange(e, data) {

            if (options.onChange && typeof options.onChange === "function") {

                /* jshint validthis:true */
                options.onChange.apply(this, [data.rating, that]);
            }
        }

        function onSet(e, data) {

            if (options.onSet && typeof options.onSet === "function") {

                /* jshint validthis:true */
                options.onSet.apply(this, [data.rating, that]);
            }
        }

        function bindEvents() {

            $node.on("mousemove", onMouseEnter)
                .on("mouseenter", onMouseEnter)
                .on("mouseleave", onMouseLeave)
                .on("click", onMouseClick)
                .on("ultimateratings.change", onChange)
                .on("ultimateratings.set", onSet);
        }

        function unbindEvents() {

            $node.off("mousemove", onMouseEnter)
                .off("mouseenter", onMouseEnter)
                .off("mouseleave", onMouseLeave)
                .off("click", onMouseClick)
                .off("ultimateratings.change", onChange)
                .off("ultimateratings.set", onSet);
        }

        function setReadOnly(newValue) {

            if (!isDefined(newValue)) {

                return options.readOnly;
            }

            options.readOnly = newValue;

            unbindEvents();

            if (!newValue) {

                bindEvents();
            }
        }

        function setRating(newValue) {

            if (!isDefined(newValue)) {

                return options.rating;
            }

            var rating = newValue;

            var maxValue = options.maxValue,
                minValue = options.minValue;

            if (typeof rating === "string") {

                if (rating[rating.length - 1] === "%") {

                    rating = rating.substr(0, rating.length - 1);
                    maxValue = setMaxValue(100);
                    minValue = setMinValue(0);
                }

                rating = parseFloat(rating);
            }

            checkBounds(rating, minValue, maxValue);

            rating = parseFloat(rating.toFixed(options.precision));

            checkPercision(parseFloat(rating), minValue, maxValue);

            options.rating = rating;

            showRating();

            $node.trigger("ultimateratings.set", {
                rating: rating
            });
        }

        function setOnSet(method) {

            if (!isDefined(method)) {

                return options.onSet;
            }

            options.onSet = method;
        }

        function setOnChange(method) {

            if (!isDefined(method)) {

                return options.onChange;
            }

            options.onChange = method;
        }

        this.rating = function(newValue) {

            if (!isDefined(newValue)) {

                return options.rating;
            }

            setRating(newValue);

            return $node;
        };

        this.destroy = function() {

            if (!options.readOnly) {
                unbindEvents();
            }

            UR_Constructor.prototype.collection = deleteInstance($node.get(0),
                this.collection);

            $node.removeClass("jq-ry-container").children().remove();

            return $node;
        };

        this.method = function(methodName) {

            if (!methodName) {

                throw Error("Method name not specified!");
            }

            if (!isDefined(this[methodName])) {

                throw Error("Method " + methodName + " doesn't exist!");
            }

            var args = Array.prototype.slice.apply(arguments, []),
                params = args.slice(1),
                method = this[methodName];

            return method.apply(this, params);
        };

        this.option = function(optionName, param) {

            if (!isDefined(optionName)) {

                return options;
            }

            var method;

            switch (optionName) {
                case "SVG":

                    method = setSVG;
                    break;

                case "svgWidth":

                    method = setStarWidth;
                    break;
                case "numIcons":

                    method = setNumStars;
                    break;
                case "normalFill":

                    method = setNormalFill;
                    break;
                case "ratedFill":

                    method = setRatedFill;
                    break;
                case "minValue":

                    method = setMinValue;
                    break;
                case "maxValue":

                    method = setMaxValue;
                    break;
                case "precision":

                    method = setPrecision;
                    break;
                case "rating":

                    method = setRating;
                    break;
                case "readOnly":

                    method = setReadOnly;
                    break;
                case "onSet":

                    method = setOnSet;
                    break;
                case "onChange":

                    method = setOnChange;
                    break;
                default:

                    throw Error("No such option as " + optionName);
            }

            method(param);

            return options[optionName];
        };

        setNumStars(options.numIcons);
        setReadOnly(options.readOnly);

        this.collection.push(this);
        this.rating(options.rating);
    }

    UR_Constructor.prototype.collection = [];

    function _UR(options) {

        var mainInstances = UR_Constructor.prototype.collection;

        /* jshint validthis:true */
        var $nodes = $(this);

        if ($nodes.length === 0) {

            return $nodes;
        }

        var args = Array.prototype.slice.apply(arguments, []);

        //console.log(RateYo.prototype.collection);

        if (args.length === 0) {

            //Setting Options to empty
            options = args[0] = {};
        } else if (args.length === 1 && typeof args[0] === "object") {

            //Setting options to first argument
            options = args[0];
        } else if (args.length >= 1 && typeof args[0] === "string") {

            var methodName = args[0],
                params = args.slice(1);

            var result = [];

            $.each($nodes, function(i, node) {

                var existingInstance = getInstance(node, mainInstances);

                if (!existingInstance) {

                    throw Error("Trying to set options before even initialization");
                }

                var method = existingInstance[methodName];

                if (!method) {

                    throw Error("Method " + methodName + " does not exist!");
                }

                var returnVal = method.apply(existingInstance, params);

                result.push(returnVal);
            });

            result = result.length === 1 ? result[0] : $(result);

            return result;
        } else {

            throw Error("Invalid Arguments");
        }

        options = $.extend(JSON.parse(JSON.stringify(DEFAULT_SETTINGS)), options);

        return $.each($nodes, function() {

            var existingInstance = getInstance(this, mainInstances);

            if (!existingInstance) {

                return new UR_Constructor($(this), options);
            }
        });
    }

    function UR() {

        /* jshint validthis:true */
        return _UR.apply(this, Array.prototype.slice.apply(arguments, []));
    }

    $.fn.UR = UR;

}(jQuery));
