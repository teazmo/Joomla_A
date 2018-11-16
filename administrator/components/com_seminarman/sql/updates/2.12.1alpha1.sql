INSERT IGNORE INTO `#__seminarman_emailtemplate` (`templatefor`, `title`, `subject`, `body`, `recipient`, `bcc`, `status`, `isdefault`) VALUES
(2, 'Wartelistenbestätigung', 'Platz auf der Warteliste für den Kurs "{COURSE_TITLE}"', '<p>Sehr geehrte(r) {SALUTATION} {TITLE}{LASTNAME},</p>\r\n<p>vielen Dank für Ihr Interesse an unserem Kurs!<br />Mit dieser E-Mail bestätigen wir Ihnen, dass sie auf der Warteliste für den Kurs {COURSE_TITLE} stehen. Außerdem sind nochmal alle Details für Sie zusammengefasst.</p>\r\n<p>Rechnungsadresse:</p>\r\n<p>{CUSTOM_COMPANY}<br />{SALUTATION} {TITLE}{FIRSTNAME} {LASTNAME}<br />{CUSTOM_STREET}<br />{CUSTOM_ZIP} {CUSTOM_CITY}<br />{CUSTOM_COUNTRY}<br /><br />Tel: {CUSTOM_PHONE}</p>\r\n<p>Der Kurs, für den Sie sich interessieren:</p>\r\n<p>Kursnr.: {COURSE_CODE}<br />Kurstitel: {COURSE_TITLE}<br />Datum: {COURSE_START_DATE} bis {COURSE_FINISH_DATE}<br />Veranstaltungsort: {COURSE_LOCATION}<br />Trainer: {TUTOR}</p>\r\n<p>Preis: {PRICE_PER_ATTENDEE_VAT} EUR (ink. {PRICE_VAT_PERCENT}% MwSt.)</p>', '{EMAIL}', '{ADMIN_CUSTOM_RECIPIENT}', NULL, 1);