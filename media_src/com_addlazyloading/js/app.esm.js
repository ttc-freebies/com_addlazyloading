import axios from 'redaxios/dist/redaxios.module'; //'https://unpkg.com/redaxios?module';
import { render, html } from 'uhtml'; //'https://unpkg.com/uhtml?module';

const button = document.getElementById('lazyLoadingButton');
const title = document.getElementById('lazyLoadingTitle');
const appContainer = document.getElementById('lazyLoadingApp');
const stepElement = document.getElementById('step');

if (!button || !appContainer || !button.dataset.url || !stepElement) {
  throw Error('Ooops the markup is meshed up...')
}

let more = false;
const urlBase = button.dataset.url;
const token = button.dataset.token;
const itemsCount = parseInt(button.dataset.itemsCount, 10);
button.removeAttribute('disabled');
button.classList.remove('btn-danger');
button.classList.add('btn-success');
button.addEventListener('click', execute);

stepElement.addEventListener('input', () => {
  const valid = stepElement.checkValidity();
  if (!valid) {
    button.setAttribute('disabled', '');
  } else {
    button.removeAttribute('disabled');
  }
});

let step = parseInt(stepElement.value, 10) || 1;

if (step < 1) {
  step = 1;
}

function paint(itemsNo, itemsCount) {
  if (!more) {
    render(appContainer, html`
<h1>Please wait while processing artiles...</h1>
<p>Item ${itemsNo} from ${itemsCount}</p>`);
  } else {
    render(appContainer, html`<h2>All Done here. Your DB is updated 🎉</h2>`);
  }

}

function execute() {
  if (title) {
    title.parentNode.removeChild(title);
  }

  button.setAttribute('disabled', '');
  stepElement.setAttribute('disabled', '');

  fetchData({
    from: 0,
    to: step,
    itemsCount: itemsCount
  });
}

const fetchData = (opts) => {
  axios({
    method: 'post',
    url: `${urlBase}administrator/index.php?option=com_addlazyloading&task=updatedb&format=json&${token}=1`,
    data: opts,
    responseType: 'json'
  })
    .then((response) => {
      if (response.status >= 200 && response.status < 300 && response.statusText === 'OK') {
        const rsp = response.data;
        paint(rsp.itemsNo, itemsCount);

        if (rsp.itemsNo === itemsCount) {
          more = true; // Done
          paint(rsp.itemsNo, itemsCount);
        } else {
          paint(rsp.itemsNo, itemsCount);

          let too;
          if (itemsCount < (rsp.from + step)) {
            too = itemsCount;
          } else {
            too = step;
          }

          fetchData({
            from: rsp.from + step,
            to: too,
            itemsCount: itemsCount
          });
        }
      }
    })
    .catch((error) => {
      console.log(error);
    });
}
