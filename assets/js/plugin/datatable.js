"use strict";

class Datatable {
    #table = null;
    #tbody = null;
    #nav = null;
    #data = null;
    #colTypes = [];
    #currentDisplayedData = null;
    #nbData = null
    #currentPage = 1;
    #orderDirection = 'asc';
    #orderColumn = null;
    #orderColumnPosition = null;
    #numericCleanerRegex = /[%]/gi;

    #defaultSettings = {
        nbElementDisplayed: 20,
        paginationNbElement: 4,
        nbPages: null,
        ajax: null,
    };

    constructor(table, settings = {}) {
        this.#table = table;
        this.#defaultSettings = {...this.#defaultSettings, ...settings};

        this.#init();
    }

    #init() {
        this.#initDatatableOrder();
        this.#addSortableEvent();

        if (this.#isClientSideDatatable()) {
            this.#initClientSideDatatable();
        } else {
            this.#initServerSideDatatable();
        }
    }

    #datatableServerSideReloadEvent() {
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
        }

        if (firstPagesInRange - 2 >= 1) {
            ul.appendChild(this.#generatePaginationNavigationBtn('...'));
        }

        for (let i = firstPagesInRange; i <= lastPagesInRange; i++) {
            ul.appendChild(this.#generatePaginationNavigationBtn(i, parseInt(current) === i));
        }

        if (lastPagesInRange + 2 <= endPage) {
            ul.appendChild(this.#generatePaginationNavigationBtn('...'));
        }

        if (lastPagesInRange + 1 <= endPage) {
            ul.appendChild(this.#generatePaginationNavigationBtn(endPage));
        }
    }

    #bindPagination() {
        this.#nav.querySelector('ul').querySelectorAll('button').forEach((el) => {
            el.addEventListener('click', () => {
                this.#currentPage = el.dataset.page;

                if(this.#isClientSideDatatable()) {
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
        if (null === this.#tbody) {
            this.#tbody = this.#table.createTBody();
        }

        if (this.#table.querySelector('tbody')) {
            this.#table.querySelector('tbody').innerHTML = '';
        }
        this.#currentDisplayedData.forEach((row) => {
            let newRow = this.#tbody.insertRow(-1);
            row.forEach((cell, index) => {
                let newCell = newRow.insertCell(index);
                newCell.innerHTML = cell;
            });
        });

        this.#drawPagination();
        this.#bindPagination();
    }

    #ajaxQuery() {
        fetch(this.#generateGetPath())
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

    #addSortableEvent() {
        this.#table.querySelectorAll('button[data-colname]').forEach((el) => {
            el.addEventListener('click', () => {
                this.#orderColumnPosition = parseInt(el.dataset.colposition);

                if (this.#orderColumn === el.dataset.colname) {
                    this.#orderDirection = this.#orderDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.#orderColumn = el.dataset.colname;
                    this.#orderDirection = 'asc';
                }

                //when order change, start at the first page
                this.#currentPage = 1;

                if (this.#isClientSideDatatable()) {
                    this.#sortData();
                    this.#findCurrentDataToDisplay();
                } else {
                    this.#ajaxQuery();
                }

                this.#updateColOrderClass();
                this.#loadDatatableContent();
            });
        });
    }

    #sortData() {
        let colType = this.#colTypes[this.#orderColumnPosition];
        let compareValues = function(a, b) {
            if ('int' === colType) {
                return 'asc' === this.#orderDirection
                    ? a[this.#orderColumnPosition] - b[this.#orderColumnPosition]
                    : -(a[this.#orderColumnPosition] - b[this.#orderColumnPosition]);
            }

            let factor = 'asc' === this.#orderDirection ? 1 : -1;

            return factor * a[this.#orderColumnPosition].localeCompare(b[this.#orderColumnPosition], undefined, {numeric: true});
        }.bind(this);

        let sortedData = [];

        //need to generate a new array of object with base data and cleaned data to perform order
        this.#data.forEach((el) => {
            let cleanedRow = [];
            el.forEach((cell, index) => {
                cleanedRow[index] = this.#colTypes[index] === 'string' ? cell : this.#numericCleaner(cell);
            });

            sortedData.push({'base': el, 'cleaned': cleanedRow});
        });

        sortedData.sort((a, b) => compareValues(a.cleaned, b.cleaned));

        this.#data = [];
        sortedData.forEach((el) => {
           this.#data.push(el.base);
        });
    }

    #updateColOrderClass() {
        this.#table.querySelectorAll('thead tr th').forEach((currentCol) => {
            currentCol.classList.remove('order-asc');
            currentCol.classList.remove('order-desc');
        });

        let col = this.#table.querySelector('thead tr th:nth-child(' + (this.#orderColumnPosition + 1) + ')');
        if (null === col) {
            return false;
        }

        col.classList.add('order-' + this.#orderDirection);
    }

    #generateGetPath() {
        let param = {
            page: this.#currentPage,
            order: this.#orderDirection,
            order_by: this.#orderColumn,
        };

        //remove null values
        param = Object.fromEntries(Object.entries(param).filter(([_, v]) => v != null));
        const searchParams = new URLSearchParams(param);

        return this.#defaultSettings.ajax + '?' + searchParams.toString()
    }

    #isClientSideDatatable() {
        return null === this.#defaultSettings.ajax || undefined === this.#defaultSettings.ajax
    }

    #initDatatableOrder() {
        this.#table.querySelectorAll('thead th').forEach((th, index) => {
            if (th.classList.contains('order-asc') || th.classList.contains('order-desc')) {
                let btn = th.querySelector('button');

                this.#orderColumn = btn.dataset.colname;
                this.#orderDirection = th.classList.contains('order-asc') ? 'asc' : 'desc';
                this.#orderColumnPosition = parseInt(btn.dataset.colposition);
            }
        });
    }

    #initServerSideDatatable() {
        this.#datatableServerSideReloadEvent();
        this.#ajaxQuery();
    }

    #initClientSideDatatable() {
        this.#data = this.#table.dataset.tabledata;

        if (null === this.#data || undefined === this.#data) {
            return;
        }

        this.#data = JSON.parse(this.#data).data;
        this.#nbData = this.#data.length;

        // detect if column contain only int
        // used for sorting action
        this.#detectColType();

        this.#findCurrentDataToDisplay();
        this.#loadDatatableContent();
    }

    #isNumber(str) {
        if (typeof str === 'number') {
            return true;
        }

        let cleaned = str.replace(this.#numericCleanerRegex, '');

        return !isNaN(cleaned);
    }

    #numericCleaner(str) {
        if (typeof str === 'number') {
            return str;
        }

        let cleaned = str.replace(this.#numericCleanerRegex, '');
        if (!isNaN(cleaned)) {
            return cleaned;
        }

        return str;
    }

    #detectColType() {
        this.#data.forEach((el) => {
           el.some((item, index) => {
                if (this.#isNumber(item)) {
                    this.#colTypes[index] = 'int';
                    return false;
                } else {
                    this.#colTypes[index] = 'string';
                }
           })
        });
    }
}

export default Datatable;
