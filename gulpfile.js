//Sass
var gulp = require('gulp');
var sass = require('gulp-sass');
var source_dir = "proj/templates/RCSE/**/**/";
var final_dir = "proj/templates/RCSE/**/**/";

gulp.task('sass', function(){
    gulp.src(source_dir + '*.scss')
    .pipe(sass())
    .pipe(gulp.dest(final_dir))
});

gulp.task('default', ['sass'], function() {
    gulp.watch(source_dir + '*.scss', ['sass'])
})