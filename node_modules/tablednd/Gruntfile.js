module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('bower.json'),
    uglify: {
      options: {
        banner: '/*! jquery.tablednd.js <%= grunt.template.today("dd-mm-yyyy") %> */\n'
      },
      dist: {
        files: {
          'dist/jquery.tablednd.min.js': ['js/jquery.tablednd.js']
        }
      }
    },
    jshint: {
      all: {
        options: {
          reporterOutput: "",
          jshintrc: true
        },
        src: 'js/*.js'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');

  grunt.registerTask('copyToDist', function() {
    grunt.file.copy('js/jquery.tablednd.js', 'dist/jquery.tablednd.js');
  });

  grunt.registerTask('test', ['jshint']);

  grunt.registerTask('default', ['jshint', 'copyToDist', 'uglify']);

};
