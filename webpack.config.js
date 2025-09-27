const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'gutenberg-ai-migration': './src/js/index.js',
	},
	output: {
		path: __dirname + '/build',
		filename: '[name].js',
	},
};
