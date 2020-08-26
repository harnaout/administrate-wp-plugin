'use strict';
var gulp = require('gulp');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
const rename = require('gulp-rename');
const del = require('del');
const minify = require('gulp-minify');
sass.compiler = require('node-sass');

gulp.task('sass-min', () => {
   return gulp.src('./assets/sass/admin.scss')
   .pipe(sass({
       errorLogToConsole: true,
       outputStyle: 'compressed'
   }).on('error', console.error.bind(console)))
   .pipe(rename({
        suffix: '.min'
    }))
   .pipe(gulp.dest('./assets/css/'));
});

gulp.task('sass', () => {
    return gulp.src('./assets/sass/admin.scss')
    .pipe(sass({
        errorLogToConsole: true
    }).on('error', console.error.bind(console)))
    .pipe(gulp.dest('./assets/css/'));
 });

gulp.task('sass:watch', () => {
    gulp.watch('./assets/sass/**/*.scss', gulp.series('sass', 'sass-min'));
});

gulp.task('js:watch', () => {
    gulp.watch('./assets/js/admin.js', gulp.series('js-min'));
});

gulp.task('watch', gulp.parallel('sass:watch'), function(done)
{
    done()
})

gulp.task('clean', () => {
    return del([
        './assets/css/*',
        './assets/js/admin/*',
    ]);
});

gulp.task('js-min', () => {
  return gulp.src('./assets/js/admin.js')
  .pipe(minify({
    ext:{
        src:'-debug.js',
        min:'.min.js'
    }
}))
    .pipe(gulp.dest('./assets/js/admin/'))
});
