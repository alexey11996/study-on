/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
import "../css/app.scss";

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
require("jquery");
require("bootstrap");
require("popper.js");

var $ = require("jquery");

console.log("Hello Webpack Encore! Edit me in assets/js/app.js");

$(document).on("change", "#course_type", function() {
  if (
    $(this)
      .find("option:selected")
      .attr("value") == "free"
  ) {
    $("#course_price").val("0");
  }
});
