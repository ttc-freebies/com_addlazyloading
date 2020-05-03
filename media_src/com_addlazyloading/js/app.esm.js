import axios from 'redaxios/dist/redaxios.module'; //'https://unpkg.com/redaxios?module';
import {render, html} from 'uhtml'; //'https://unpkg.com/uhtml?module';

const button = document.getElementById('lazyLoadingButton');
const title = document.getElementById('lazyLoadingTitle');
const appContainer = document.getElementById('lazyLoadingApp');
const dataElement = document.getElementById('lazyLoadingCategories');
let categories, step = 10, more;

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
    if (!more) {
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
${more}
`);
    } else {
        render(appContainer, more);
    }

}

function skipEmptyCategories(old) {
    if (categories[old].articlesCount === 0) return [old, old];

    let isSet = false;
    let newIdx = 0;
    for (let i = old + 1; i < categories.length; i++) {
        if (categories[i].articlesCount > 0 && !isSet) {
            isSet = true;
            newIdx = i;
            break;
        } else {
            categories[i].done = true;
        }
    }

    return [old, newIdx];
}


function execute() {
    if (title) title.parentNode.removeChild(title);
    button.setAttribute('disabled', '');
    const newIdx = skipEmptyCategories(0);
    paint(categories);
    fetchData({
            catId: categories[newIdx[1]].id,
            from: 0,
            to: step > categories[newIdx[1]].articlesCount ? categories[newIdx[1]].articlesCount : step,
            requested: newIdx[1],
        },
        newIdx[1]
    );
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

                if (requested + 1 === categories.length) {
                    paint(categories);
                    return;
                }

                if (rsp.request.to === categories[requested].articlesCount) {
                    const newIdx = skipEmptyCategories(requested + 1);
                    categories[newIdx[0]].done = true;
                    more = (!categories[categories.length - 1].done) ? '' : html`<h2>All Done here. Your DB is updated</h2>`;


                    paint(categories);
                    fetchData({
                            catId: categories[newIdx[1]].id,
                            from: 0,
                            to: step > categories[newIdx[1]].articlesCount ? categories[newIdx[1]].articlesCount : step,
                            requested: requested + 1,
                        },
                        newIdx[1]);
                } else {
                    paint(categories);
                    fetchData({
                            catId: categories[requested].id,
                            from: rsp.request.to,
                            to: step > categories[requested].articlesCount ? categories[requested].articlesCount : rsp.request.to + step,
                            requested: requested,
                        },
                        requested);
                }
            }
        })
        .catch((error) => {
            console.log(error);
        });
}
