.mode json
SELECT l.itemcode AS itemcode, l.label_i18n_ta AS label_i18n_ta, l.label_i18n_si AS label_i18n_si, s.desc AS label_i18n_default FROM label_i18n l JOIN sih_current s ON s.itemcode = l.itemcode;
