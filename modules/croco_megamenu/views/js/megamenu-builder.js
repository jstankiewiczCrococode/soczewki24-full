(function () {
  'use strict';

  var HANDLE = '<span class="mmb-handle" aria-hidden="true">&#8942;&#8942;</span>';

  function el(tag, className, attrs) {
    var node = document.createElement(tag);
    if (className) {
      node.className = className;
    }
    Object.keys(attrs || {}).forEach(function (key) {
      node.setAttribute(key, attrs[key]);
    });
    return node;
  }

  function input(className, placeholder, value) {
    var node = el('input', 'form-control ' + className, {type: 'text', placeholder: placeholder});
    node.value = value || '';
    return node;
  }

  function removeButton(title, onClick) {
    var button = el('button', 'btn btn-link mmb-remove', {type: 'button', title: title});
    button.innerHTML = '<i class="icon-trash"></i>';
    button.addEventListener('click', onClick);
    return button;
  }

  function Builder(root) {
    this.root = root;
    this.field = root.querySelector('#columns_json');
    this.columnsHost = root.querySelector('.megamenu-builder__columns');
    this.columnCount = parseInt(root.dataset.columns, 10) || 4;

    try {
      this.labels = JSON.parse(root.dataset.labels);
    } catch (e) {
      this.labels = {};
    }

    this.render(this.readState());
    this.bindForm();
  }

  Builder.prototype.readState = function () {
    var state;

    try {
      state = JSON.parse(this.field.value || '[]');
    } catch (e) {
      state = [];
    }

    if (!Array.isArray(state)) {
      state = [];
    }

    while (state.length < this.columnCount) {
      state.push({blocks: []});
    }

    return state.slice(0, this.columnCount);
  };

  Builder.prototype.render = function (state) {
    this.columnsHost.innerHTML = '';

    state.forEach(function (column, index) {
      this.columnsHost.appendChild(this.buildColumn(column, index));
    }, this);

    this.serialize();
  };

  Builder.prototype.buildColumn = function (column, index) {
    var node = el('div', 'mmb-column');
    var head = el('div', 'mmb-column__head');
    head.textContent = (this.labels.column || 'Kolumna') + ' ' + (index + 1);

    var blocks = el('div', 'mmb-column__blocks');
    (column.blocks || []).forEach(function (block) {
      blocks.appendChild(this.buildBlock(block));
    }, this);

    var actions = el('div', 'mmb-column__actions');
    actions.appendChild(this.buildAddBlock(blocks));

    node.appendChild(head);
    node.appendChild(blocks);
    node.appendChild(actions);

    this.makeSortable(blocks, 'mmb-blocks');

    return node;
  };

  Builder.prototype.buildAddBlock = function (blocksHost) {
    var wrapper = el('div', 'mmb-add-block');
    var select = el('select', 'form-control mmb-add-block__type');

    [
      ['links', this.labels.addLinks || 'Grupa linkow'],
      ['cta', this.labels.addCta || 'CTA'],
      ['banner', this.labels.addBanner || 'Baner'],
    ].forEach(function (pair) {
      var option = el('option', null, {value: pair[0]});
      option.textContent = pair[1];
      select.appendChild(option);
    });

    var button = el('button', 'btn btn-default mmb-add-block__button', {type: 'button'});
    button.innerHTML = '<i class="icon-plus"></i> ' + (this.labels.addBlock || 'Dodaj blok');
    button.addEventListener('click', function () {
      blocksHost.appendChild(this.buildBlock({type: select.value, title: '', links: []}));
      this.serialize();
    }.bind(this));

    wrapper.appendChild(select);
    wrapper.appendChild(button);

    return wrapper;
  };

  Builder.prototype.buildBlock = function (block) {
    var type = block.type || 'links';
    var node = el('div', 'mmb-block mmb-block--' + type, {'data-type': type});

    var head = el('div', 'mmb-block__head');
    head.innerHTML = HANDLE;

    var badge = el('span', 'mmb-block__badge');
    badge.textContent = this.typeLabel(type);
    head.appendChild(badge);
    head.appendChild(removeButton(this.labels.remove || 'Usun', function () {
      node.remove();
      this.serialize();
    }.bind(this)));

    node.appendChild(head);

    if (type === 'links') {
      node.appendChild(input('mmb-block__title', this.labels.blockTitle || '', block.title));

      var links = el('div', 'mmb-block__links');
      (block.links || []).forEach(function (link) {
        links.appendChild(this.buildLink(link, type));
      }, this);
      node.appendChild(links);

      var add = el('button', 'btn btn-link mmb-add-link', {type: 'button'});
      add.innerHTML = '<i class="icon-plus"></i> ' + (this.labels.addLink || 'Dodaj link');
      add.addEventListener('click', function () {
        links.appendChild(this.buildLink({}, type));
        this.serialize();
      }.bind(this));
      node.appendChild(add);

      this.makeSortable(links, 'mmb-links');
    } else {
      var single = el('div', 'mmb-block__links');
      single.appendChild(this.buildLink((block.links || [])[0] || {}, type));
      node.appendChild(single);
    }

    return node;
  };

  Builder.prototype.buildLink = function (link, blockType) {
    var node = el('div', 'mmb-link');

    if (blockType === 'links') {
      node.innerHTML = HANDLE;
    }

    if (blockType === 'banner') {
      node.appendChild(input('mmb-link__image', this.labels.bannerImage || '', link.image));
      node.appendChild(input('mmb-link__label', this.labels.bannerText || '', link.label));
    } else {
      node.appendChild(input('mmb-link__label', this.labels.linkLabel || '', link.label));
    }

    node.appendChild(input('mmb-link__url', this.labels.linkUrl || '', link.url));

    if (blockType === 'links') {
      node.appendChild(removeButton(this.labels.remove || 'Usun', function () {
        node.remove();
        this.serialize();
      }.bind(this)));
    }

    return node;
  };

  Builder.prototype.typeLabel = function (type) {
    if (type === 'banner') {
      return this.labels.typeBanner || 'Baner';
    }
    if (type === 'cta') {
      return this.labels.typeCta || 'CTA';
    }
    return this.labels.typeLinks || 'Grupa linkow';
  };

  Builder.prototype.makeSortable = function (host, group) {
    if (typeof window.Sortable === 'undefined') {
      return;
    }

    window.Sortable.create(host, {
      group: group,
      handle: '.mmb-handle',
      animation: 150,
      fallbackOnBody: true,
      onEnd: this.serialize.bind(this),
    });
  };

  Builder.prototype.serialize = function () {
    var state = [];

    this.root.querySelectorAll('.mmb-column').forEach(function (columnNode) {
      var blocks = [];

      columnNode.querySelectorAll('.mmb-block').forEach(function (blockNode) {
        var type = blockNode.dataset.type;
        var titleNode = blockNode.querySelector('.mmb-block__title');
        var links = [];

        blockNode.querySelectorAll('.mmb-link').forEach(function (linkNode) {
          var label = linkNode.querySelector('.mmb-link__label');
          var url = linkNode.querySelector('.mmb-link__url');
          var image = linkNode.querySelector('.mmb-link__image');

          links.push({
            label: label ? label.value : '',
            url: url ? url.value : '',
            image: image ? image.value : '',
          });
        });

        blocks.push({
          type: type,
          title: titleNode ? titleNode.value : '',
          links: links,
        });
      });

      state.push({blocks: blocks});
    });

    this.field.value = JSON.stringify(state);
  };

  Builder.prototype.bindForm = function () {
    this.root.addEventListener('input', this.serialize.bind(this));

    var form = this.root.closest('form');
    if (form) {
      form.addEventListener('submit', this.serialize.bind(this));
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.megamenu-builder').forEach(function (root) {
      new Builder(root);
    });
  });
})();
