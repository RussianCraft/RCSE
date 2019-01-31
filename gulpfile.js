//Sass
var gulp = require("gulp");
var sass = require("gulp-sass");
var sourceDir = "public_html/themes/RCS-New/pages/**/";
var finalDir = "public_html/themes/RCS-New/pages/";

gulp.task("sass", function(){
    gulp.src(sourceDir + "structure.scss")
    .pipe(sass({outputStyle: "expanded"}))
    .pipe(gulp.dest(finalDir))
});

gulp.task("default", ["sass"], function () {
    gulp.watch(source_dir + "*.scss", ["sass"])
});