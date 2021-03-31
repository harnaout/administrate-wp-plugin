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

gulp.task('admwpp-sass-min', () => {
    return gulp.src('./assets/sass/admwpp.scss')
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

 gulp.task('admwpp-sass', () => {
    return gulp.src('./assets/sass/admwpp.scss')
    .pipe(sass({
        errorLogToConsole: true
    }).on('error', console.error.bind(console)))
    .pipe(gulp.dest('./assets/css/'));
 });

gulp.task('sass:watch', () => {
    gulp.watch('./assets/sass/**/*.scss', gulp.series('sass', 'sass-min', 'admwpp-sass', 'admwpp-sass-min'));
});

gulp.task('js:watch', () => {
    gulp.watch([
        './assets/js/common/*',
        './assets/js/admin/*',
        './assets/js/admwpp/*',
        './assets/js/admin.js',
        './assets/js/admwpp.js',
    ], gulp.series('js-admin-min', 'js-admwpp-min'));
});

gulp.task('watch', gulp.parallel('sass:watch', 'js:watch'), function(done)
{
    done()
})

gulp.task('clean', () => {
    return del([
        './assets/css/*',
        './assets/js/admin/*',
    ]);
});

gulp.task('js-admin-min', () => {
  return gulp.src([
        './assets/js/common/*',
        './assets/js/admin/*',
        './assets/js/admin.js',
    ])
  .pipe(concat('admin.js'))
  .pipe(minify({
    ext:{
        src:'-debug.js',
        min:'.min.js'
    }
}))
    .pipe(gulp.dest('./assets/js/'))
});

gulp.task('js-admwpp-min', () => {
    return gulp.src([
          './assets/js/admwpp/*',
          './assets/js/admwpp.js',
      ])
    .pipe(concat('admwpp.js'))
    .pipe(minify({
      ext:{
          src:'-debug.js',
          min:'.min.js'
      }
  }))
      .pipe(gulp.dest('./assets/js/'))
  });
