window.addEventListener('load', function () {
  'use strict';

  document.querySelectorAll('.btn-add-collection').forEach(btn => {
    addCollectionRowEvent(btn);
  });

  document.querySelectorAll('.btn-remove-collection').forEach(btn => {
    removeCollectionRowEvent(btn);
  });

  function addCollectionRowEvent(el) {
    if (null !== el) {
      el.addEventListener('click', function () {
        let collectionHolder = el.closest('.collection-widget').querySelectorAll('.collection-list');

        if (collectionHolder.length) {
          addCollectionRow(collectionHolder[0]);
        }
      });
    }
  }

  function removeCollectionRowEvent(el) {
    if (null !== el) {
      el.addEventListener('click', function () {
        el.closest('.collection-item').remove();
      });
    }
  }

  function addCollectionRow(collectionHolder) {
    let prototype = collectionHolder.getAttribute('data-prototype');
    let index = collectionHolder.getAttribute('data-index');
    let newRow = prototype.replace(/__name__/g, index);

    let div = document.createElement('div');
    div.innerHTML = newRow.trim();

    let event = new CustomEvent('collection-append-new-item', {
      'detail': div.firstChild
    });

    removeCollectionRowEvent(div.firstChild.querySelector('.btn-remove-collection'));
    addCollectionRowEvent(div.firstChild.querySelector('.btn-add-collection'));

    collectionHolder.setAttribute('data-index', Number(index) + 1);
    collectionHolder.append(div.firstChild);
    collectionHolder.dispatchEvent(event);
  }
});
