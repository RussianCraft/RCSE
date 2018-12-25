//Sass
var gulp = require('gulp');
var sass = require('gulp-sass');
var source_dir = "public_html/themes/RCS-New/pages/**/";
var final_dir = "public_html/themes/RCS-New/pages/";

gulp.task('sass', function(){
    gulp.src(source_dir + 'structure.scss')
    .pipe(sass())
    .pipe(gulp.dest(final_dir))
});

gulp.task('default', ['sass'], function() {
    gulp.watch(source_dir + '*.scss', ['sass'])
})