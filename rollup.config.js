
import resolve from '@rollup/plugin-node-resolve';
import minifyHTML from 'rollup-plugin-minify-html-literals';
import babel from '@rollup/plugin-babel';
import { terser } from "rollup-plugin-terser";
import commonjs from '@rollup/plugin-commonjs';

export default [{
  input: 'media_src/com_addlazyloading/js/app.esm.js',
  plugins: [
    resolve({module: true}),
    minifyHTML(),
    terser(),
  ],
  context: 'null',
  moduleContext: 'null',
  output: [
    { file: 'package/media/com_addlazyloading/js/app.esm.js', format: 'es' }
  ]
  },{
    input: 'media_src/com_addlazyloading/js/app.esm.js',
    plugins: [
    resolve({
      browser: true
    }),
    babel({
        babelrc: false,
        babelHelpers: 'runtime',
        exclude: 'node_modules/**',
        plugins: ['@babel/plugin-transform-runtime'],
        presets: [
          [
            "@babel/preset-env",
            {
              useBuiltIns: 'usage',
              corejs: '3.0.0',
              "targets": {
                "browsers":  [
                  "last 1 version",
                  "> 1%",
                  "IE 11"
                ]
              },
              debug: true,
            },
          ]
        ],
      }),
    commonjs({
      include: 'node_modules/**'
    }),
    minifyHTML(),
    terser(),
  ],
  // context: 'null',
  // moduleContext: 'null',
    output: [
      { file: 'package/media/com_addlazyloading/js/app.es5.js', format: 'iife' }
    ]
}];
