//Sass
var gulp = require("gulp");
var sass = require("gulp-sass");
var sourceDir = "public_html/resources/themes/RCS/pages/**/";
var finalDir = "public_html/resources/themes/RCS/pages/";

gulp.task("sass", function(){
    gulp.src(sourceDir + "style.scss")
        .pipe(sass({ outputStyle: "expanded" }))
        .pipe(gulp.dest(finalDir));
});

gulp.task("default", ["sass"], function () {
    gulp.watch(sourceDir + "*.scss", ["sass"]);
});