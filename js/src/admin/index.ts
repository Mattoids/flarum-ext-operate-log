import app from 'flarum/admin/app';

app.initializers.add('mattoid-operate-log', () => {
  app.extensionData.for("mattoid-operate-log")
    .registerSetting({
      setting: 'mattoid-operate-log.request-type-get',
      help: app.translator.trans('mattoid-operate-log.admin.settings.request-type-get-requirement'),
      label: app.translator.trans('mattoid-operate-log.admin.settings.request-type-get'),
      type: 'switch',
      default: false
    })
    .registerSetting({
      setting: 'mattoid-operate-log.request-type-post',
      help: app.translator.trans('mattoid-operate-log.admin.settings.type-requirement'),
      label: app.translator.trans('mattoid-operate-log.admin.settings.request-type-post'),
      type: 'switch',
      default: true
    })
    .registerSetting({
      setting: 'mattoid-operate-log.request-type-put',
      help: app.translator.trans('mattoid-operate-log.admin.settings.type-requirement'),
      label: app.translator.trans('mattoid-operate-log.admin.settings.request-type-put'),
      type: 'switch',
      default: true
    })
    .registerSetting({
      setting: 'mattoid-operate-log.request-type-delete',
      help: app.translator.trans('mattoid-operate-log.admin.settings.type-requirement'),
      label: app.translator.trans('mattoid-operate-log.admin.settings.request-type-delete'),
      type: 'switch',
      default: true
    })
});
