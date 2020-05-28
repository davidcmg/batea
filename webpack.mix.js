const mix = require('laravel-mix');

mix.setPublicPath(path.resolve('./'));

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .sass('resources/sass/landing.scss', 'public/css')
   .copyDirectory('resources/js/jqCron/src/','public/js/jqCron')
   .version()
   .browserSync({
      proxy: 'batea.local',
      host: 'batea.local',
      open: false,
      notify: true,
      ui:false
   });