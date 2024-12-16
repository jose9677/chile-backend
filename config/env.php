<?php 

return [
    'truncate' => 3,

    //ROLES...
    'neighbor' => 1,
    'socialAssistant' => 2,
    'supervisor' => 3,
    'admin' => 4,
    'support' => 5,

    //STATUS...
    'inReview' => 1,
    'pendingAppointment' => 2,
    'assignedAppointment' => 3,
    'confirmedAppointment' => 4,
    'toReprogram' => 5,
    'approved' => 6,
    'rejected' => 7,
    'pending' => 8,
    'cancelled' => 9,
    'genered' => 10,
    'blocked' => 11,
    'renewedUpdated' => 12,
    'pendingRenew' => 13,
    'expired' => 14,
    'renewed' => 15,

    //MOTIVES...
    'personWithSevereDependency' => 1,
    'personWithDisability' => 2,
    'kid' => 3,

    //CANCEL MOTIVES...
    'costVeryHigh' => 1,
    'allServicesInsufficient' => 2,
    'itWasATest' => 3,

    //APS_STATUS...
    'cleanCurrentList' => 1,
    'loadNewList' => 2,
    'backupMainFielToServer' => 3,
    'updateNeighborsRegisteredWithAps' => 4,
    'updateDiscountRate' => 5,
    'processFinished' => 6,

    //DES_APS_STATUS...
    'finishedSuccessdull' => 1,
    'inProcess' => 2,
    'pendingAPS' => 3,
    'finishedWithError' => 4,

    //CATEGORIES...
    'consults' => 1,
    'testLab' => 2,
    'image' => 3,
    'kinesiology' => 4,
    'procedures' => 5,
    'mindHealth' => 6,
    'videoConsult' => 7,

    //DEDICATIONS...
    'partTime' => 1,
    'fullTime' => 2,

    //DOCUMENTS_TYPE...
    'domicile' => 1,
    'prevition' => 2,
    'profilePic' => 3,

    //MOD_STATUS
    'pendingMod' => 1,
    'approvedMod' => 2,
    'modicated' => 3,
    'rejectedMod' => 4,

    //MOD_STATUS_ACTION...
    'add' => 1,
    'delete' => 2,

    //PREVENTIONS_DETAILS...

    //PREVENTIONS_TYPES...
    'FONASA' => 1,
    'ISAPRE' => 2,
    'OTRO' => 3,
    'NINGUNO' => 4,

    //PROPERTY_TYPES...
    'home' => 1,
    'departament' => 2,
    'office' => 3,

    //PROVIDERS...
    'INTEGRAMEDICA' => 1,

    //REMUNERATIONS...
    'paid' => 1,
    'noPaid' => 2,

    //SEX...
    'male' => 1,
    'female' => 2,
    'iPreferNotTosay' => 3,

    //DOWNLOAD FILES...
    'reports' => 'public/reports/',
    'integramedica' => 'public/integramedica/'
];