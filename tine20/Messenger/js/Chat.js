Ext.ns('Tine.Messenger');

Tine.Messenger.Chat = Ext.extend(Ext.Window, {
    
    constructor: function () {
        Ext.apply(this, {
            iconCls:     'messenger-icon',
            width:       350,
            height:      300,
            closeAction: 'hide', //'close' - destroy the component
            plain:       true,
            items: [
                {
                    xtype: 'textarea',
                    style: 'width: 100%;'
                },
                {
                    xtype: 'textfield',
                    style: 'width: 100%;'
                }
            ]
        });

        Tine.Messenger.Chat.superclass.constructor.apply(this, arguments);
    }
    
});