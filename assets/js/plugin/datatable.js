"use strict";

class Datatable {
    #table = null;
    #nav = null;
    #data = null
    #currentDisplayedData = null;
    #nbData = null
    #currentPage = 1;

    #defaultSettings = {
        nbElementDisplayed: 20,
        paginationNbElement: 4,
        nbPages: null,
        ajax: null,
    };

    constructor(table, settings = {}) {
        this.#table = table;
        this.#data = null;
        this.#defaultSettings = {...this.#defaultSettings, ...settings};

        if (null === this.#defaultSettings.ajax || undefined === this.#defaultSettings.ajax) {
            this.#data = table.dataset.tabledata;

            if (null === this.#data || undefined === this.#data) {
                return;
            }

            this.#data = JSON.parse(this.#data).data;
            this.#nbData = this.#data.length;

            this.#findCurrentDataToDisplay();
            this.#loadDatatableContent(1);
        } else {
            this.#datatableReloadEvent();
            this.#ajaxQuery(1);
        }
    }

    #datatableReloadEvent() {
        this.#table.addEventListener('datatable-ajax-query-done', (event) => {
            this.#nbData = event.detail.recordsTotal;
            this.#data = event.detail.data;
            this.#currentDisplayedData = event.detail.data;

            this.#loadDatatableContent(event.detail.currentPage);
        });
    }

    #drawPagination() {
        let current = parseInt(this.#currentPage);

        let limit = parseInt(this.#defaultSettings.nbElementDisplayed);
        let nbPageItem = parseInt(this.#defaultSettings.paginationNbElement);

        let endPage = Math.ceil(this.#nbData / limit);
        let firstPagesInRange = Math.max(1, current - nbPageItem / 2);
        let lastPagesInRange = Math.min(endPage, current + nbPageItem / 2);

        if (null !== this.#nav) {
            this.#nav.remove();
            this.#nav = null;
        }

        this.#nav = document.createElement('div');
        this.#nav.classList.add('datatable-pagination');
        this.#table.after(this.#nav);

        let ul = document.createElement('ul');
        this.#nav.appendChild(ul);

        if (firstPagesInRange - 1 >= 1) {
            ul.appendChild(this.#generatePaginationNavigationBtn(1));
            ul.appendChild(this.#generatePaginationNavigationBtn('...'));
        }

        for (let i = firstPagesInRange; i <= lastPagesInRange; i++) {
            ul.appendChild(this.#generatePaginationNavigationBtn(i, parseInt(current) === i));
        }

        if (lastPagesInRange + 1 <= endPage) {
            ul.appendChild(this.#generatePaginationNavigationBtn('...'));
            ul.appendChild(this.#generatePaginationNavigationBtn(endPage));
        }

        ul.querySelectorAll('button').forEach((el) => {
            el.addEventListener('click', () => {
                this.#currentPage = el.dataset.page;

                if(null === this.#defaultSettings.ajax || undefined === this.#defaultSettings.ajax) {
                    this.#findCurrentDataToDisplay();
                    this.#loadDatatableContent();
                } else {
                    this.#ajaxQuery();
                }
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

    #loadDatatableContent() {
        if (this.#table.querySelector('tbody')) {
            this.#table.querySelector('tbody').innerHTML = '';
        }

        this.#currentDisplayedData.forEach((row) => {
            let newRow = this.#table.insertRow(-1);
            row.forEach((cell, index) => {
                let newCell = newRow.insertCell(index);
                newCell.innerHTML = cell;
            });
        });

        this.#drawPagination();
    }

    #ajaxQuery() {
        fetch(this.#defaultSettings.ajax + '?page=' + this.#currentPage)
            .then(response => response.json())
            .then(response => {
                let event = new CustomEvent('datatable-ajax-query-done', {
                    'detail': response,
                });
                this.#table.dispatchEvent(event);
            })
        ;
    }

    #findCurrentDataToDisplay() {
        let nbElements = parseInt(this.#defaultSettings.nbElementDisplayed);
        this.#currentDisplayedData = this.#data.slice((this.#currentPage - 1) * nbElements, (this.#currentPage - 1) * nbElements + nbElements);
    }
}
export default Datatable;
