var path = require('path');
var CollectElementSettingsModule = require('./CollectElementsSettings');
// var ExtractTextPlugin = require('extract-text-webpack-plugin');
module.exports = {
	context: path.resolve(__dirname, "public"),
	entry: {
		node: './main',
		wp: './wp-main'
	},
	output: {
		path: './public/assets/', // Assets dist path
		publicPath: './assets/', // Used to generate URL's
		filename: '../[name].bundle.js' // Main bundle file
	},
	plugins: [
			new CollectElementSettingsModule()
	],
	module: {
		loaders: [
			{ test: /\.js$/, loader: 'babel', include: path.resolve(__dirname, 'public') },
			{ test: /\.less$/, loader: 'style-loader!css-loader!less-loader' }, // use ! to chain loaders
			{ test: /\.css$/, loader: 'style-loader!css-loader' },
			{ test: /\.(png|jpe?g|gif)$/, loader: 'url-loader?limit=8192' }, // inline base64 URLs for <=8k images, direct URLs for the rest
			{ test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/, loader: "url-loader?limit=10000&mimetype=application/font-woff" },
			{ test: /\.(ttf|eot|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/, loader: "file-loader" },
			{ test: require.resolve("react"), loader: "expose?React" },
			// **IMPORTANT** This is needed so that each bootstrap js file required by
			// bootstrap-webpack has access to the jQuery object
			{ test: /bootstrap\/js\//, loader: 'imports?jQuery=jquery' }
		]
	}
};