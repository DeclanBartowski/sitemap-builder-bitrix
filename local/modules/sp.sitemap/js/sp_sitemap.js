function popup() {
  this.Dialog = new BX.CDialog()
}

popup.prototype = {
  showStaticFile: function () {
    this.modal('Статические разделы', '/local/modules/sp.sitemap/ajax/getStatic.php', this.save)
  },
  showInfoBlock: function () {
    this.modal('Инфоблоки', '/local/modules/sp.sitemap/ajax/getIBlock.php', this.save)
  },
  showModal: function (title, type) {
    this.modal(title, '/local/modules/sp.sitemap/ajax/getModal.php?type=' + type, this.save)

  },
  save: {
    title: 'Cохранить',
    id: 'savebtn',
    name: 'savebtn',
    className: 'adm-btn-save',
    action: function () {
      let parentWindow = this.parentWindow;
      let current = popupModal;
      $.post($('#form_modal_sitemap').attr('action'), $('#form_modal_sitemap').serialize(), function (data) {
        parentWindow.Close();
        current.refresh();
      });
    }
  },
  modal: function (title, url, callback) {
    this.Dialog = new BX.CDialog({
      'title': title,
      'content_url': url,
      'buttons': [callback, BX.CDialog.btnClose]
    }).Show();
  },
  delete: function (el) {

    $(el).parents('tr').remove();
  },
  deleteStatic: function (el, id, contentID) {
    let current = this;
    $.post('/local/modules/sp.sitemap/ajax/delete.php', {id: id}, function (data) {
      current.refresh();
    });
  },
  refresh: function () {
    let contentID = '#' + $('#tabControl_active_tab').val() + '_edit_table';
    $.get(location.href, function (data) {
      $(contentID).html($(data).find(contentID).html());
    });
  },
  update: function (element) {
    $.post('/local/modules/sp.sitemap/ajax/update.php', $(element).parents('form').serialize(), function (data) {
      let currentUrl = location.href;
      let newUrl;
      let currentTab = $('[name="tabControl_active_tab"]').val();
      if (currentUrl.indexOf('tabControl_active_tab=') !== -1) {
        newUrl = currentUrl.replace(/tabControl_active_tab=.*/, 'tabControl_active_tab=' + currentTab);
      } else if (currentUrl.indexOf('?') > 0) {
        newUrl = currentUrl + '&tabControl_active_tab=' + currentTab;
      } else {
        newUrl = currentUrl + '?tabControl_active_tab=' + currentTab;
      }
      location.replace(newUrl);
    });
  },
}
var popupModal = new popup();
