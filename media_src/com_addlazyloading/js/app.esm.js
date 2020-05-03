// import axios from 'https://unpkg.com/redaxios?module';
// import {render, html} from 'https://unpkg.com/uhtml?module';
import axios from 'redaxios/dist/redaxios.module';
import {render, html} from 'uhtml';

const button = document.getElementById('lazyLoadingButton');
const title = document.getElementById('lazyLoadingTitle');
const appContainer = document.getElementById('lazyLoadingApp');
const dataElement = document.getElementById('lazyLoadingCategories');
let categories;
let step = 10;

if (!button || !appContainer || !button.dataset.url || !dataElement) {
    throw Error('Ooops the markup is meshed up...')
}

try {
    categories = JSON.parse(dataElement.textContent);
} catch {
    throw new Error('oops Json is not valid')
}

if (!categories) {
    throw new Error('No categories?')
}

const urlBase = button.dataset.url;
const token = button.dataset.token;

// Set some initial state
categories.map(c => { c.done = false });
button.removeAttribute('disabled');
button.classList.remove('btn-danger');
button.classList.add('btn-success');
button.addEventListener('click', execute);

function paint(cats) {
        render(appContainer, html`
<h1>Please wait while processing categories...</h1>
<table class="table table-striped">
<thead>
    <tr>
        <td class="nowrap">Processed</td>
        <td class="nowrap">Name</td>
        <td class="nowrap">ID</td>
        <td class="nowrap">Articles processed</td>
    </tr>
</thead>
<tbody>
${cats.map(ct => html`
    <tr>
        <td class="nowrap">${ct.done ? '👍' : '❌' }</td>
        <td class="nowrap">${ct.title || ''}</td>
        <td class="nowrap">${ct.id || ''}</td>
        <td class="nowrap">${ct.articlesCount || 0}</td>
    </tr>
`)}
</tbody>
</table>
`);
}

function skipEmptyCategories(idx) {
    if (categories[idx].articlesCount > 0) return idx;

    let isSet = false;
    let newIdx = 0;
    for (let i = idx + 1; categories.length; i++) {
        if (categories[i].articlesCount > 0 && !isSet) {
            isSet = true;
            newIdx = i;
        }
    }

    return newIdx;
}


function execute() {
    if (title) title.parentNode.removeChild(title);
    button.setAttribute('disabled', '');
    paint(categories);

    const newIdx = skipEmptyCategories(0);

    fetchData({
            catId: categories[newIdx].id,
            path: categories[newIdx].path,
            from: 0,
            to: step > categories[newIdx].articlesCount ? categories[newIdx].articlesCount : step,
            requested: newIdx,
            isLast: categories[newIdx].isLast
        },
        newIdx);
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
                const requested = rsp.request.requested;

                if (rsp.request.to === categories[requested].articlesCount) {
                    categories[requested].done = rsp.category.done;

                    if (requested + 1 > categories.length) return;

                    const newIdx = skipEmptyCategories(requested + 1);

                    fetchData({
                            catId: categories[newIdx].id,
                            path: categories[newIdx].path,
                            from: 0,
                            to: step > categories[newIdx].articlesCount ? categories[newIdx].articlesCount : step,
                            requested: requested + 1,
                            isLast: categories[newIdx].isLast
                        },
                        newIdx);
                } else {
                    fetchData({
                            catId: categories[requested].id,
                            path: categories[requested].path,
                            from: rsp.request.to,
                            to: step > categories[requested].articlesCount ? categories[requested].articlesCount : rsp.request.to + step,
                            requested: requested,
                            isLast: categories[requested].isLast
                        },
                        requested);
                }

                paint(categories);
            }
        })
        .catch((error) => {
            console.log(error);
        })
        .then(function () {
            paint(categories);
        });
}
