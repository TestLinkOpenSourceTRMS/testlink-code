/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * File Name: he.js
 * 	Hebrew language file.
 * 
 * File Authors:
 * 		Ophir Radnitz (ophir@liqweed.net)
 */

var FCKLang =
{
// Language direction : "ltr" (left to right) or "rtl" (right to left).
Dir					: "rtl",

ToolbarCollapse		: "כיווץ סרגל הכלים",
ToolbarExpand		: "פתיחת סרגל הכלים",

// Toolbar Items and Context Menu
Save				: "שמירה",
NewPage				: "דף חדש",
Preview				: "תצוגה מקדימה",
Cut					: "גזירה",
Copy				: "העתקה",
Paste				: "הדבקה",
PasteText			: "הדבקה כטקסט פשוט",
PasteWord			: "הדבקה מ-Word",
Print				: "הדפסה",
SelectAll			: "בחירת הכל",
RemoveFormat		: "הסרת העיצוב",
InsertLinkLbl		: "קישור",
InsertLink			: "הוספת/עריכת קישור",
RemoveLink			: "הסרת הקישור",
Anchor				: "Insert/Edit Anchor",	//MISSING
InsertImageLbl		: "תמונה",
InsertImage			: "הוספת/עריכת תמונה",
InsertFlashLbl		: "Flash",	//MISSING
InsertFlash			: "Insert/Edit Flash",	//MISSING
InsertTableLbl		: "טבלה",
InsertTable			: "הוספת/עריכת טבלה",
InsertLineLbl		: "קו",
InsertLine			: "הוספת קו אופקי",
InsertSpecialCharLbl: "תו מיוחד",
InsertSpecialChar	: "הוספת תו מיוחד",
InsertSmileyLbl		: "סמיילי",
InsertSmiley		: "הוספת סמיילי",
About				: "אודות FCKeditor",
Bold				: "מודגש",
Italic				: "נטוי",
Underline			: "קו תחתון",
StrikeThrough		: "כתיב מחוק",
Subscript			: "כתיב תחתון",
Superscript			: "כתיב עליון",
LeftJustify			: "יישור לשמאל",
CenterJustify		: "מרכוז",
RightJustify		: "יישור לימין",
BlockJustify		: "יישור לשוליים",
DecreaseIndent		: "הקטנת אינדנטציה",
IncreaseIndent		: "הגדלת אינדנטציה",
Undo				: "ביטול צעד אחרון",
Redo				: "חזרה על צעד אחרון",
NumberedListLbl		: "רשימה ממוספרת",
NumberedList		: "הוספת/הסרת רשימה ממוספרת",
BulletedListLbl		: "רשימת נקודות",
BulletedList		: "הוספת/הסרת רשימת נקודות",
ShowTableBorders	: "הצגת מסגרת הטבלה",
ShowDetails			: "הצגת פרטים",
Style				: "סגנון",
FontFormat			: "עיצוב",
Font				: "גופן",
FontSize			: "גודל",
TextColor			: "צבע טקסט",
BGColor				: "צבע רקע",
Source				: "מקור",
Find				: "חיפוש",
Replace				: "החלפה",
SpellCheck			: "Check Spell",	//MISSING
UniversalKeyboard	: "Universal Keyboard",	//MISSING

Form			: "Form",	//MISSING
Checkbox		: "Checkbox",	//MISSING
RadioButton		: "Radio Button",	//MISSING
TextField		: "Text Field",	//MISSING
Textarea		: "Textarea",	//MISSING
HiddenField		: "Hidden Field",	//MISSING
Button			: "Button",	//MISSING
SelectionField	: "Selection Field",	//MISSING
ImageButton		: "Image Button",	//MISSING

// Context Menu
EditLink			: "עריכת קישור",
InsertRow			: "הוספת שורה",
DeleteRows			: "מחיקת שורות",
InsertColumn		: "הוספת עמודה",
DeleteColumns		: "מחיקת עמודות",
InsertCell			: "הוספת תא",
DeleteCells			: "מחיקת תאים",
MergeCells			: "מיזוג תאים",
SplitCell			: "פיצול תאים",
CellProperties		: "תכונות התא",
TableProperties		: "תכונות הטבלה",
ImageProperties		: "תכונות התמונה",
FlashProperties		: "Flash Properties",	//MISSING

AnchorProp			: "Anchor Properties",	//MISSING
ButtonProp			: "Button Properties",	//MISSING
CheckboxProp		: "Checkbox Properties",	//MISSING
HiddenFieldProp		: "Hidden Field Properties",	//MISSING
RadioButtonProp		: "Radio Button Properties",	//MISSING
ImageButtonProp		: "Image Button Properties",	//MISSING
TextFieldProp		: "Text Field Properties",	//MISSING
SelectionFieldProp	: "Selection Field Properties",	//MISSING
TextareaProp		: "Textarea Properties",	//MISSING
FormProp			: "Form Properties",	//MISSING

FontFormats			: "נורמלי;קוד;כתובת;כותרת;כותרת 2;כותרת 3;כותרת 4;כותרת 5;כותרת 6",

// Alerts and Messages
ProcessingXHTML		: "מעבד XHTML, נא להמתין...",
Done				: "המשימה הושלמה",
PasteWordConfirm	: "נראה הטקסט שבכוונתך להדביק מקורו בקובץ Word. האם ברצונך לנקות אותו טרם ההדבקה?",
NotCompatiblePaste	: "פעולה זו זמינה לדפדפן Internet Explorer מגירסא 5.5 ומעלה. האם להמשיך בהדבקה ללא הניקוי?",
UnknownToolbarItem	: "פריט לא ידוע בסרגל הכלים \"%1\"",
UnknownCommand		: "שם פעולה לא ידוע \"%1\"",
NotImplemented		: "הפקודה לא מיושמת",
UnknownToolbarSet	: "ערכת סרגל הכלים \"%1\" לא קיימת",

// Dialogs
DlgBtnOK			: "אישור",
DlgBtnCancel		: "ביטול",
DlgBtnClose			: "סגירה",
DlgBtnBrowseServer	: "Browse Server",	//MISSING
DlgAdvancedTag		: "אפשרויות מתקדמות",
DlgOpOther			: "&lt;Other&gt;",	//MISSING
DlgInfoTab			: "Info",	//MISSING
DlgAlertUrl			: "Please insert the URL",	//MISSING

// General Dialogs Labels
DlgGenNotSet		: "&lt;לא נקבע&gt;",
DlgGenId			: "זיהוי (Id)",
DlgGenLangDir		: "כיוון שפה",
DlgGenLangDirLtr	: "שמאל לימין (LTR)",
DlgGenLangDirRtl	: "ימין לשמאל (RTL)",
DlgGenLangCode		: "קוד שפה",
DlgGenAccessKey		: "מקש גישה",
DlgGenName			: "שם",
DlgGenTabIndex		: "מספר טאב",
DlgGenLongDescr		: "קישור לתיאור מפורט",
DlgGenClass			: "Stylesheet Classes",
DlgGenTitle			: "כותרת מוצעת",
DlgGenContType		: "Content Type מוצע",
DlgGenLinkCharset	: "קידוד המשאב המקושר",
DlgGenStyle			: "סגנון",

// Image Dialog
DlgImgTitle			: "תכונות התמונה",
DlgImgInfoTab		: "מידע על התמונה",
DlgImgBtnUpload		: "שליחה לשרת",
DlgImgURL			: "כתובת (URL)",
DlgImgUpload		: "העלאה",
DlgImgAlt			: "טקסט חלופי",
DlgImgWidth			: "רוחב",
DlgImgHeight		: "גובה",
DlgImgLockRatio		: "נעילת היחס",
DlgBtnResetSize		: "איפוס הגודל",
DlgImgBorder		: "מסגרת",
DlgImgHSpace		: "מרווח אופקי",
DlgImgVSpace		: "מרווח אנכי",
DlgImgAlign			: "יישור",
DlgImgAlignLeft		: "לשמאל",
DlgImgAlignAbsBottom: "לתחתית האבסולוטית",
DlgImgAlignAbsMiddle: "מרכוז אבסולוטי",
DlgImgAlignBaseline	: "לקו התחתית",
DlgImgAlignBottom	: "לתחתית",
DlgImgAlignMiddle	: "לאמצע",
DlgImgAlignRight	: "לימין",
DlgImgAlignTextTop	: "לראש הטקסט",
DlgImgAlignTop		: "למעלה",
DlgImgPreview		: "תצוגה מקדימה",
DlgImgAlertUrl		: "נא להקליד את כתובת התמונה",
DlgImgLinkTab		: "Link",	//MISSING

// Flash Dialog
DlgFlashTitle		: "Flash Properties",	//MISSING
DlgFlashChkPlay		: "Auto Play",	//MISSING
DlgFlashChkLoop		: "Loop",	//MISSING
DlgFlashChkMenu		: "Enable Flash Menu",	//MISSING
DlgFlashScale		: "Scale",	//MISSING
DlgFlashScaleAll	: "Show all",	//MISSING
DlgFlashScaleNoBorder	: "No Border",	//MISSING
DlgFlashScaleFit	: "Exact Fit",	//MISSING

// Link Dialog
DlgLnkWindowTitle	: "קישור",
DlgLnkInfoTab		: "מידע על הקישור",
DlgLnkTargetTab		: "מטרה",

DlgLnkType			: "סוג קישור",
DlgLnkTypeURL		: "כתובת (URL)",
DlgLnkTypeAnchor	: "עוגן בעמוד זה",
DlgLnkTypeEMail		: "דוא''ל",
DlgLnkProto			: "פרוטוקול",
DlgLnkProtoOther	: "&lt;אחר&gt;",
DlgLnkURL			: "כתובת (URL)",
DlgLnkAnchorSel		: "בחירת עוגן",
DlgLnkAnchorByName	: "עפ''י שם העוגן",
DlgLnkAnchorById	: "עפ''י זיהוי (Id) הרכיב",
DlgLnkNoAnchors		: "&lt;אין עוגנים זמינים בדף&gt;",
DlgLnkEMail			: "כתובת הדוא''ל",
DlgLnkEMailSubject	: "נושא ההודעה",
DlgLnkEMailBody		: "גוף ההודעה",
DlgLnkUpload		: "העלאה",
DlgLnkBtnUpload		: "שליחה לשרת",

DlgLnkTarget		: "מטרה",
DlgLnkTargetFrame	: "&lt;frame&gt;",
DlgLnkTargetPopup	: "&lt;חלון קופץ&gt;",
DlgLnkTargetBlank	: "חלון חדש (_blank)",
DlgLnkTargetParent	: "חלון האב (_parent)",
DlgLnkTargetSelf	: "באותו החלון (_self)",
DlgLnkTargetTop		: "חלון ראשי (_top)",
DlgLnkTargetFrameName	: "Target Frame Name",	//MISSING
DlgLnkPopWinName	: "שם החלון הקופץ",
DlgLnkPopWinFeat	: "תכונות החלון הקופץ",
DlgLnkPopResize		: "בעל גודל ניתן לשינוי",
DlgLnkPopLocation	: "סרגל כתובת",
DlgLnkPopMenu		: "סרגל תפריט",
DlgLnkPopScroll		: "ניתן לגלילה",
DlgLnkPopStatus		: "סרגל חיווי",
DlgLnkPopToolbar	: "סרגל הכלים",
DlgLnkPopFullScrn	: "מסך מלא (IE)",
DlgLnkPopDependent	: "תלוי (Netscape)",
DlgLnkPopWidth		: "רוחב",
DlgLnkPopHeight		: "גובה",
DlgLnkPopLeft		: "מיקום צד שמאל",
DlgLnkPopTop		: "מיקום צד עליון",

DlnLnkMsgNoUrl		: "נא להקליד את כתובת הקישור (URL)",
DlnLnkMsgNoEMail	: "נא להקליד את כתובת הדוא''ל",
DlnLnkMsgNoAnchor	: "נא לבחור עוגן במסמך",

// Color Dialog
DlgColorTitle		: "בחירת צבע",
DlgColorBtnClear	: "איפוס",
DlgColorHighlight	: "נוכחי",
DlgColorSelected	: "נבחר",

// Smiley Dialog
DlgSmileyTitle		: "הוספת סמיילי",

// Special Character Dialog
DlgSpecialCharTitle	: "בחירת תו מיוחד",

// Table Dialog
DlgTableTitle		: "תכונות טבלה",
DlgTableRows		: "שורות",
DlgTableColumns		: "עמודות",
DlgTableBorder		: "גודל מסגרת",
DlgTableAlign		: "יישור",
DlgTableAlignNotSet	: "<לא נקבע>",
DlgTableAlignLeft	: "שמאל",
DlgTableAlignCenter	: "מרכז",
DlgTableAlignRight	: "ימין",
DlgTableWidth		: "רוחב",
DlgTableWidthPx		: "פיקסלים",
DlgTableWidthPc		: "אחוז",
DlgTableHeight		: "גובה",
DlgTableCellSpace	: "מרווח תא",
DlgTableCellPad		: "ריפוד תא",
DlgTableCaption		: "כיתוב",

// Table Cell Dialog
DlgCellTitle		: "תכונות תא",
DlgCellWidth		: "רוחב",
DlgCellWidthPx		: "פיקסלים",
DlgCellWidthPc		: "אחוז",
DlgCellHeight		: "גובה",
DlgCellWordWrap		: "גלילת שורות",
DlgCellWordWrapNotSet	: "<לא נקבע>",
DlgCellWordWrapYes	: "כן",
DlgCellWordWrapNo	: "לא",
DlgCellHorAlign		: "יישור אופקי",
DlgCellHorAlignNotSet	: "<לא נקבע>",
DlgCellHorAlignLeft	: "שמאל",
DlgCellHorAlignCenter	: "מרכז",
DlgCellHorAlignRight: "ימין",
DlgCellVerAlign		: "יישור אנכי",
DlgCellVerAlignNotSet	: "<לא נקבע>",
DlgCellVerAlignTop	: "למעלה",
DlgCellVerAlignMiddle	: "לאמצע",
DlgCellVerAlignBottom	: "לתחתית",
DlgCellVerAlignBaseline	: "קו תחתית",
DlgCellRowSpan		: "טווח שורות",
DlgCellCollSpan		: "טווח עמודות",
DlgCellBackColor	: "צבע רקע",
DlgCellBorderColor	: "צבע מסגרת",
DlgCellBtnSelect	: "בחירה...",

// Find Dialog
DlgFindTitle		: "חיפוש",
DlgFindFindBtn		: "חיפוש",
DlgFindNotFoundMsg	: "הטקסט המבוקש לא נמצא.",

// Replace Dialog
DlgReplaceTitle			: "החלפה",
DlgReplaceFindLbl		: "חיפוש מחרוזת:",
DlgReplaceReplaceLbl	: "החלפה במחרוזת:",
DlgReplaceCaseChk		: "התאמת סוג אותיות (Case)",
DlgReplaceReplaceBtn	: "החלפה",
DlgReplaceReplAllBtn	: "החלפה בכל העמוד",
DlgReplaceWordChk		: "התאמה למילה המלאה",

// Paste Operations / Dialog
PasteErrorPaste	: "הגדרות האבטחה בדפדפן שלך לא מאפשרות לעורך לבצע פעולות הדבקה אוטומטיות. יש להשתמש במקלדת לשם כך (Ctrl+V).",
PasteErrorCut	: "הגדרות האבטחה בדפדפן שלך לא מאפשרות לעורך לבצע פעולות גזירה  אוטומטיות. יש להשתמש במקלדת לשם כך (Ctrl+X).",
PasteErrorCopy	: "הגדרות האבטחה בדפדפן שלך לא מאפשרות לעורך לבצע פעולות העתקה אוטומטיות. יש להשתמש במקלדת לשם כך (Ctrl+C).",

PasteAsText		: "הדבקה כטקסט פשוט",
PasteFromWord	: "הדבקה מ-Word",

DlgPasteMsg2	: "Please paste inside the following box using the keyboard (<STRONG>Ctrl+V</STRONG>) and hit <STRONG>OK</STRONG>.",	//MISSING
DlgPasteIgnoreFont		: "Ignore Font Face definitions",	//MISSING
DlgPasteRemoveStyles	: "Remove Styles definitions",	//MISSING
DlgPasteCleanBox		: "Clean Up Box",	//MISSING


// Color Picker
ColorAutomatic	: "אוטומטי",
ColorMoreColors	: "צבעים נוספים...",

// Document Properties
DocProps		: "Document Properties",	//MISSING

// Anchor Dialog
DlgAnchorTitle		: "Anchor Properties",	//MISSING
DlgAnchorName		: "Anchor Name",	//MISSING
DlgAnchorErrorName	: "Please type the anchor name",	//MISSING

// Speller Pages Dialog
DlgSpellNotInDic		: "Not in dictionary",	//MISSING
DlgSpellChangeTo		: "Change to",	//MISSING
DlgSpellBtnIgnore		: "Ignore",	//MISSING
DlgSpellBtnIgnoreAll	: "Ignore All",	//MISSING
DlgSpellBtnReplace		: "Replace",	//MISSING
DlgSpellBtnReplaceAll	: "Replace All",	//MISSING
DlgSpellBtnUndo			: "Undo",	//MISSING
DlgSpellNoSuggestions	: "- No suggestions -",	//MISSING
DlgSpellProgress		: "Spell check in progress...",	//MISSING
DlgSpellNoMispell		: "Spell check complete: No misspellings found",	//MISSING
DlgSpellNoChanges		: "Spell check complete: No words changed",	//MISSING
DlgSpellOneChange		: "Spell check complete: One word changed",	//MISSING
DlgSpellManyChanges		: "Spell check complete: %1 words changed",	//MISSING

IeSpellDownload			: "Spell checker not installed. Do you want to download it now?",	//MISSING

// Button Dialog
DlgButtonText	: "Text (Value)",	//MISSING
DlgButtonType	: "Type",	//MISSING

// Checkbox and Radio Button Dialogs
DlgCheckboxName		: "Name",	//MISSING
DlgCheckboxValue	: "Value",	//MISSING
DlgCheckboxSelected	: "Selected",	//MISSING

// Form Dialog
DlgFormName		: "Name",	//MISSING
DlgFormAction	: "Action",	//MISSING
DlgFormMethod	: "Method",	//MISSING

// Select Field Dialog
DlgSelectName		: "Name",	//MISSING
DlgSelectValue		: "Value",	//MISSING
DlgSelectSize		: "Size",	//MISSING
DlgSelectLines		: "lines",	//MISSING
DlgSelectChkMulti	: "Allow multiple selections",	//MISSING
DlgSelectOpAvail	: "Available Options",	//MISSING
DlgSelectOpText		: "Text",	//MISSING
DlgSelectOpValue	: "Value",	//MISSING
DlgSelectBtnAdd		: "Add",	//MISSING
DlgSelectBtnModify	: "Modify",	//MISSING
DlgSelectBtnUp		: "Up",	//MISSING
DlgSelectBtnDown	: "Down",	//MISSING
DlgSelectBtnSetValue : "Set as selected value",	//MISSING
DlgSelectBtnDelete	: "Delete",	//MISSING

// Textarea Dialog
DlgTextareaName	: "Name",	//MISSING
DlgTextareaCols	: "Columns",	//MISSING
DlgTextareaRows	: "Rows",	//MISSING

// Text Field Dialog
DlgTextName			: "Name",	//MISSING
DlgTextValue		: "Value",	//MISSING
DlgTextCharWidth	: "Character Width",	//MISSING
DlgTextMaxChars		: "Maximum Characters",	//MISSING
DlgTextType			: "Type",	//MISSING
DlgTextTypeText		: "Text",	//MISSING
DlgTextTypePass		: "Password",	//MISSING

// Hidden Field Dialog
DlgHiddenName	: "Name",	//MISSING
DlgHiddenValue	: "Value",	//MISSING

// Bulleted List Dialog
BulletedListProp	: "Bulleted List Properties",	//MISSING
NumberedListProp	: "Numbered List Properties",	//MISSING
DlgLstType			: "Type",	//MISSING
DlgLstTypeCircle	: "Circle",	//MISSING
DlgLstTypeDisk		: "Disk",	//MISSING
DlgLstTypeSquare	: "Square",	//MISSING
DlgLstTypeNumbers	: "Numbers (1, 2, 3)",	//MISSING
DlgLstTypeLCase		: "Lowercase Letters (a, b, c)",	//MISSING
DlgLstTypeUCase		: "Uppercase Letters (A, B, C)",	//MISSING
DlgLstTypeSRoman	: "Small Roman Numerals (i, ii, iii)",	//MISSING
DlgLstTypeLRoman	: "Large Roman Numerals (I, II, III)",	//MISSING

// Document Properties Dialog
DlgDocGeneralTab	: "General",	//MISSING
DlgDocBackTab		: "Background",	//MISSING
DlgDocColorsTab		: "Colors and Margins",	//MISSING
DlgDocMetaTab		: "Meta Data",	//MISSING

DlgDocPageTitle		: "Page Title",	//MISSING
DlgDocLangDir		: "Language Direction",	//MISSING
DlgDocLangDirLTR	: "Left to Right (LTR)",	//MISSING
DlgDocLangDirRTL	: "Right to Left (RTL)",	//MISSING
DlgDocLangCode		: "Language Code",	//MISSING
DlgDocCharSet		: "Character Set Encoding",	//MISSING
DlgDocCharSetOther	: "Other Character Set Encoding",	//MISSING

DlgDocDocType		: "Document Type Heading",	//MISSING
DlgDocDocTypeOther	: "Other Document Type Heading",	//MISSING
DlgDocIncXHTML		: "Include XHTML Declarations",	//MISSING
DlgDocBgColor		: "Background Color",	//MISSING
DlgDocBgImage		: "Background Image URL",	//MISSING
DlgDocBgNoScroll	: "Nonscrolling Background",	//MISSING
DlgDocCText			: "Text",	//MISSING
DlgDocCLink			: "Link",	//MISSING
DlgDocCVisited		: "Visited Link",	//MISSING
DlgDocCActive		: "Active Link",	//MISSING
DlgDocMargins		: "Page Margins",	//MISSING
DlgDocMaTop			: "Top",	//MISSING
DlgDocMaLeft		: "Left",	//MISSING
DlgDocMaRight		: "Right",	//MISSING
DlgDocMaBottom		: "Bottom",	//MISSING
DlgDocMeIndex		: "Document Indexing Keywords (comma separated)",	//MISSING
DlgDocMeDescr		: "Document Description",	//MISSING
DlgDocMeAuthor		: "Author",	//MISSING
DlgDocMeCopy		: "Copyright",	//MISSING
DlgDocPreview		: "Preview",	//MISSING

// Templates Dialog
Templates			: "Templates",	//MISSING
DlgTemplatesTitle	: "Content Templates",	//MISSING
DlgTemplatesSelMsg	: "Please select the template to open in the editor<br>(the actual contents will be lost):",	//MISSING
DlgTemplatesLoading	: "Loading templates list. Please wait...",	//MISSING
DlgTemplatesNoTpl	: "(No templates defined)",	//MISSING

// About Dialog
DlgAboutAboutTab	: "About",	//MISSING
DlgAboutBrowserInfoTab	: "Browser Info",	//MISSING
DlgAboutVersion		: "גירסא",
DlgAboutLicense		: "ברשיון תחת תנאי GNU Lesser General Public License",
DlgAboutInfo		: "מידע נוסף ניתן למצוא כאן:"
}