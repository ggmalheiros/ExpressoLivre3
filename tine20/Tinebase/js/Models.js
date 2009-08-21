/*
 * Tine 2.0
 * 
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 *
 */
 
Ext.namespace('Tine', 'Tine.Tinebase', 'Tine.Tinebase.Model');

/**
 * @type {Array}
 * generic Record fields
 */
Tine.Tinebase.Model.genericFields = [
    { name: 'container_id', header: 'Container'                                     },
    { name: 'creation_time',      type: 'date', dateFormat: Date.patterns.ISO8601Long},
    { name: 'created_by',         type: 'int'                  },
    { name: 'last_modified_time', type: 'date', dateFormat: Date.patterns.ISO8601Long},
    { name: 'last_modified_by',   type: 'int'                  },
    { name: 'is_deleted',         type: 'boolean'              },
    { name: 'deleted_time',       type: 'date', dateFormat: Date.patterns.ISO8601Long},
    { name: 'deleted_by',         type: 'int'                  }
];
    
/**
 * Model of the tine (simple) user account
 */
Tine.Tinebase.Model.User = Ext.data.Record.create([
    { name: 'accountId' },
    { name: 'accountDisplayName' },
    { name: 'accountLastName' },
    { name: 'accountFirstName' },
    { name: 'accountFullName' },
    { name: 'contact_id' }
]);

/**
 * Model of a language
 */
Tine.Tinebase.Model.Language = Ext.data.Record.create([
    { name: 'locale' },
    { name: 'language' },
    { name: 'region' }
]);

/**
 * Model of a timezone
 */
Tine.Tinebase.Model.Timezone = Ext.data.Record.create([
    { name: 'timezone' },
    { name: 'timezoneTranslation' }
]);

/**
 * @namespace Tine.Tinebase.Model
 * @class     Tine.Tinebase.Model.Group
 * @extends   Tine.Tinebase.data.Record
 * 
 * Model of a user group
 */
Tine.Tinebase.Model.Group = Tine.Tinebase.data.Record.create([
    {name: 'id'},
    {name: 'name'},
    {name: 'description'}
    //{name: 'groupMembers'}
], {
    appName: 'Tinebase',
    modelName: 'Group',
    idProperty: 'id',
    titleProperty: 'name',
    // ngettext('Group', 'Groups', n); gettext('Groups');
    recordName: 'Group',
    recordsName: 'Groups',
    containerProperty: null
});

/**
 * Model of a role
 */
Tine.Tinebase.Model.Role = Ext.data.Record.create([
    {name: 'id'},
    {name: 'name'},
    {name: 'description'}
]);

/**
 * Model of a generalised account (user or group)
 */
Tine.Tinebase.Model.Account = Ext.data.Record.create([
    {name: 'id'},
    {name: 'type'},
    {name: 'name'},
    {name: 'data'} // todo: throw away data
]);

/**
 * Model of a container
 */
Tine.Tinebase.Model.Container = Ext.data.Record.create([
    {name: 'id'},
    {name: 'name'},
    {name: 'type'},
    {name: 'backend'},
    {name: 'application_id'},
    {name: 'account_grants'}
]);

/**
 * Model of a grant
 */
Tine.Tinebase.Model.Grant = Ext.data.Record.create([
    {name: 'id'},
    {name: 'account_id'},
    {name: 'account_type'},
    {name: 'account_name'},
    {name: 'readGrant',   type: 'boolean'},
    {name: 'addGrant',    type: 'boolean'},
    {name: 'editGrant',   type: 'boolean'},
    {name: 'deleteGrant', type: 'boolean'},
    {name: 'adminGrant',  type: 'boolean'}
]);

/**
 * Model of a tag
 * 
 * @constructor {Ext.data.Record}
 */
Tine.Tinebase.Model.Tag = Ext.data.Record.create([
    {name: 'id'         },
    {name: 'app'        },
    {name: 'owner'      },
    {name: 'name'       },
    {name: 'type'       },
    {name: 'description'},
    {name: 'color'      },
    {name: 'occurrence' },
    {name: 'rights'     },
    {name: 'contexts'   }
]);

/**
 * Model of a PickerRecord
 * 
 * @constructor {Ext.data.Record}
 */
Tine.Tinebase.PickerRecord = Ext.data.Record.create([
    {name: 'id'}, 
    {name: 'name'}, 
    {name: 'data'}
]);

/**
 * Model of a note
 * 
 * @constructor {Ext.data.Record}
 */
Tine.Tinebase.Model.Note = Ext.data.Record.create([
    {name: 'id'             },
    {name: 'note_type_id'   },
    {name: 'note'           },
    {name: 'creation_time', type: 'date', dateFormat: Date.patterns.ISO8601Long },
    {name: 'created_by'     }
]);

/**
 * Model of a note type
 * 
 * @constructor {Ext.data.Record}
 */
Tine.Tinebase.Model.NoteType = Ext.data.Record.create([
    {name: 'id'             },
    {name: 'name'           },
    {name: 'icon'           },
    {name: 'icon_class'     },
    {name: 'description'    },
    {name: 'is_user_type'   }
]);

/**
 * Model of a customfield definition
 */
Tine.Tinebase.Model.Customfield = Ext.data.Record.create([
    { name: 'application_id' },
    { name: 'id'             },
    { name: 'model'          },
    { name: 'name'           },
    { name: 'label'          },
    { name: 'type'           },
    { name: 'length'         },
    { name: 'group'          },
    { name: 'order'          }
]);

/**
 * Model of a preference
 * 
 * @constructor {Ext.data.Record}
 */
Tine.Tinebase.Model.Preference = Ext.data.Record.create([
    {name: 'id'             },
    {name: 'name'           },
    {name: 'value'          },
    {name: 'type'           },
    {name: 'label'          },
    {name: 'description'    },
    {name: 'options'        }
]);

/**
 * Model of an alarm
 * 
 * @constructor {Ext.data.Record}
 */
Tine.Tinebase.Model.Alarm = Ext.data.Record.create([
    {name: 'id'             },
    {name: 'record_id'      },
    {name: 'model'          },
    {name: 'alarm_time'     },
    {name: 'minutes_before' },
    {name: 'sent_time'      },
    {name: 'sent_status'    },
    {name: 'sent_message'   },
    {name: 'options'        }
]);
