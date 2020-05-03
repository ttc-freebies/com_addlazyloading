
import resolve from '@rollup/plugin-node-resolve';
import minifyHTML from 'rollup-plugin-minify-html-literals';
import { terser } from "rollup-plugin-terser";

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
    input: 'media_src/com_addlazyloading/js/app.es5.js',
    output: [
      { file: 'package/media/com_addlazyloading/js/app.es5.js', format: 'iife' }
    ]
}];