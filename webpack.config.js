const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    mode: 'production',
    entry:  {
        'datagrid': './assets/src/datagrid',
        'datagrid-spinners': './assets/src/datagrid-spinners',
        'datagrid-instant-url-refresh': './assets/src/datagrid-instant-url-refresh',
    },
    externals: {
        jquery: 'jQuery',
        netteAjax: 'jQuery.nette',
        nette: 'Nette',
        naja: 'naja'
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel-loader' 
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[id].css'
        })
    ],
    output: {
        path: path.resolve(__dirname, 'assets/dist'),
        filename: '[name].js'
    }
};