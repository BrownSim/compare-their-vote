"use strict";

class Datatable {
    #nav = null;
    #defaultSettings = {
        nbElement: 20,
        paginationNbElement: 4
    };

    constructor(table, settings = {}) {
        let data = table.dataset.tabledata;

        this.#defaultSettings = {...this.#defaultSettings, ...settings};

        if (null === data) {
            return;
        }

        if (data !== undefined) {
            data = JSON.parse(data);
        }

        this.#loadDatatableContent(table, data.data, 1, this.#defaultSettings.nbElement);
    }

    #drawPagination(table, data, current = 1) {
        current = parseInt(current);

        let limit = parseInt(this.#defaultSettings.nbElement);
        let nbPageItem = parseInt(this.#defaultSettings.paginationNbElement);

        let endPage = Math.ceil(data.length / limit);
        let firstPagesInRange = Math.max(1, current - nbPageItem / 2);
        let lastPagesInRange = Math.min(endPage, current + nbPageItem / 2);

        if (null !== this.#nav) {
            this.#nav.remove();
            this.#nav = null;
        }

        this.#nav = document.createElement('div');
        this.#nav.classList.add('datatable-pagination');
        table.after(this.#nav);

        let ul = document.createElement('ul');
        this.#nav.appendChild(ul);

        if (firstPagesInRange - 2 >= 1) {
            ul.appendChild(this.#generatePaginationNavigationBtn(1));
        }

        if (firstPagesInRange - 1 >= 1) {
            ul.appendChild(this.#generatePaginationNavigationBtn('...'));
        }

        for (let i = firstPagesInRange; i <= lastPagesInRange; i++) {
            ul.appendChild(this.#generatePaginationNavigationBtn(i, parseInt(current) === i));
        }

        if (lastPagesInRange + 1 < endPage) {
            ul.appendChild(this.#generatePaginationNavigationBtn('...'));
        }

        if (lastPagesInRange + 2 < endPage) {
            ul.appendChild(this.#generatePaginationNavigationBtn(endPage));
        }

        ul.querySelectorAll('button').forEach((el) => {
            el.addEventListener('click', () => {
                this.#loadDatatableContent(table, data, el.dataset.page);
            });
        });
    }

    #generatePaginationNavigationBtn(label, active = false) {
        let li = document.createElement('li');
        let btn = document.createElement('button');

        btn.innerHTML = label;
        btn.classList.add('btn');
        btn.classList.add(active ? 'btn-primary' : 'btn-light');

        if ('...' !== label) {
            btn.dataset.page = label;
        } else {
            btn.classList.add('disabled');
        }

        li.appendChild(btn);

        return li
    }

    #loadDatatableContent(table, data, page) {
        let nbElements = parseInt(this.#defaultSettings.nbElement);

        if (table.querySelector('tbody')) {
            table.querySelector('tbody').innerHTML = '';
        }

        const slicedData = data.slice((page - 1) * nbElements, (page - 1) * nbElements + nbElements);
        slicedData.forEach((row) => {
            let newRow = table.insertRow(-1);
            row.forEach((cell, index) => {
                let newCell = newRow.insertCell(index);
                newCell.innerHTML = cell;
            });
        })

        this.#drawPagination(table, data, page);
    }
}

export default Datatable;
