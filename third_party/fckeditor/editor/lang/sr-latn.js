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
 * File Name: sr-latn.js
 * 	Serbian (Latin) language file.
 * 
 * File Authors:
 * 		Zoran Subic (zoran@tf.zr.ac.yu)
 */

var FCKLang =
{
// Language direction : "ltr" (left to right) or "rtl" (right to left).
Dir					: "ltr",

ToolbarCollapse		: "Smanji liniju sa alatkama",
ToolbarExpand		: "Proiri liniju sa alatkama",

// Toolbar Items and Context Menu
Save				: "Sacuvaj",
NewPage				: "Nova stranica",
Preview				: "Izgled stranice",
Cut					: "Iseci",
Copy				: "Kopiraj",
Paste				: "Zalepi",
PasteText			: "Zalepi kao neformatiran tekst",
PasteWord			: "Zalepi iz Worda",
Print				: "tampa",
SelectAll			: "Oznaci sve",
RemoveFormat		: "Ukloni formatiranje",
InsertLinkLbl		: "Link",
InsertLink			: "Unesi/izmeni link",
RemoveLink			: "Ukloni link",
Anchor				: "Unesi/izmeni sidro",
InsertImageLbl		: "Slika",
InsertImage			: "Unesi/izmeni sliku",
InsertFlashLbl		: "Fle",
InsertFlash			: "Unesi/izmeni fle",
InsertTableLbl		: "Tabela",
InsertTable			: "Unesi/izmeni tabelu",
InsertLineLbl		: "Linija",
InsertLine			: "Unesi horizontalnu liniju",
InsertSpecialCharLbl: "Specijalni karakteri",
InsertSpecialChar	: "Unesi specijalni karakter",
InsertSmileyLbl		: "Smajli",
InsertSmiley		: "Unesi smajlija",
About				: "O FCKeditoru",
Bold				: "Podebljano",
Italic				: "Kurziv",
Underline			: "Podvuceno",
StrikeThrough		: "Precrtano",
Subscript			: "Indeks",
Superscript			: "Stepen",
LeftJustify			: "Levo ravnanje",
CenterJustify		: "Centriran tekst",
RightJustify		: "Desno ravnanje",
BlockJustify		: "Obostrano ravnanje",
DecreaseIndent		: "Smanji levu marginu",
IncreaseIndent		: "Uvecaj levu marginu",
Undo				: "Poniti akciju",
Redo				: "Ponovi akciju",
NumberedListLbl		: "Nabrojiva lista",
NumberedList		: "Unesi/ukloni nabrojivu listu",
BulletedListLbl		: "Nenabrojiva lista",
BulletedList		: "Unesi/ukloni nenabrojivu listu",
ShowTableBorders	: "Prikai okvir tabele",
ShowDetails			: "Prikai detalje",
Style				: "Stil",
FontFormat			: "Format",
Font				: "Font",
FontSize			: "Velicina fonta",
TextColor			: "Boja teksta",
BGColor				: "Boja pozadine",
Source				: "K&ocirc;d",
Find				: "Pretraga",
Replace				: "Zamena",
SpellCheck			: "Proveri spelovanje",
UniversalKeyboard	: "Univerzalna tastatura",

Form			: "Forma",
Checkbox		: "Polje za potvrdu",
RadioButton		: "Radio-dugme",
TextField		: "Tekstualno polje",
Textarea		: "Zona teksta",
HiddenField		: "Skriveno polje",
Button			: "Dugme",
SelectionField	: "Izborno polje",
ImageButton		: "Dugme sa slikom",

// Context Menu
EditLink			: "Izmeni link",
InsertRow			: "Unesi red",
DeleteRows			: "Obrii redove",
InsertColumn		: "Unesi kolonu",
DeleteColumns		: "Obrii kolone",
InsertCell			: "Unesi celije",
DeleteCells			: "Obrii celije",
MergeCells			: "Spoj celije",
SplitCell			: "Razdvoji celije",
CellProperties		: "Osobine celije",
TableProperties		: "Osobine tabele",
ImageProperties		: "Osobine slike",
FlashProperties		: "Osobine flea",

AnchorProp			: "Osobine sidra",
ButtonProp			: "Osobine dugmeta",
CheckboxProp		: "Osobine polja za potvrdu",
HiddenFieldProp		: "Osobine skrivenog polja",
RadioButtonProp		: "Osobine radio-dugmeta",
ImageButtonProp		: "Osobine dugmeta sa slikom",
TextFieldProp		: "Osobine tekstualnog polja",
SelectionFieldProp	: "Osobine izbornog polja",
TextareaProp		: "Osobine zone teksta",
FormProp			: "Osobine forme",

FontFormats			: "Normal;Formatirano;Adresa;Heading 1;Heading 2;Heading 3;Heading 4;Heading 5;Heading 6",

// Alerts and Messages
ProcessingXHTML		: "Obradujem XHTML. Malo strpljenja...",
Done				: "Zavrio",
PasteWordConfirm	: "Tekst koji elite da nalepite kopiran je iz Worda. Da li elite da bude ocicen od formata pre lepljenja?",
NotCompatiblePaste	: "Ova komanda je dostupna samo za Internet Explorer od verzije 5.5. Da li elite da nalepim tekst bez cicenja?",
UnknownToolbarItem	: "Nepoznata stavka toolbara \"%1\"",
UnknownCommand		: "Nepoznata naredba \"%1\"",
NotImplemented		: "Naredba nije implementirana",
UnknownToolbarSet	: "Toolbar \"%1\" ne postoji",

// Dialogs
DlgBtnOK			: "OK",
DlgBtnCancel		: "Otkai",
DlgBtnClose			: "Zatvori",
DlgBtnBrowseServer	: "Pretrai server",
DlgAdvancedTag		: "Napredni tagovi",
DlgOpOther			: "&lt;Ostali&gt;",
DlgInfoTab			: "Info",
DlgAlertUrl			: "Molimo Vas, unesite URL",

// General Dialogs Labels
DlgGenNotSet		: "&lt;nije postavljeno&gt;",
DlgGenId			: "Id",
DlgGenLangDir		: "Smer jezika",
DlgGenLangDirLtr	: "S leva na desno (LTR)",
DlgGenLangDirRtl	: "S desna na levo (RTL)",
DlgGenLangCode		: "K&ocirc;d jezika",
DlgGenAccessKey		: "Pristupni taster",
DlgGenName			: "Naziv",
DlgGenTabIndex		: "Tab indeks",
DlgGenLongDescr		: "Pun opis URL",
DlgGenClass			: "Stylesheet klase",
DlgGenTitle			: "Advisory naslov",
DlgGenContType		: "Advisory vrsta sadraja",
DlgGenLinkCharset	: "Linked Resource Charset",
DlgGenStyle			: "Stil",

// Image Dialog
DlgImgTitle			: "Osobine slika",
DlgImgInfoTab		: "Info slike",
DlgImgBtnUpload		: "Poalji na server",
DlgImgURL			: "URL",
DlgImgUpload		: "Poalji",
DlgImgAlt			: "Alternativni tekst",
DlgImgWidth			: "irina",
DlgImgHeight		: "Visina",
DlgImgLockRatio		: "Zakljucaj odnos",
DlgBtnResetSize		: "Resetuj velicinu",
DlgImgBorder		: "Okvir",
DlgImgHSpace		: "HSpace",
DlgImgVSpace		: "VSpace",
DlgImgAlign			: "Ravnanje",
DlgImgAlignLeft		: "Levo",
DlgImgAlignAbsBottom: "Abs dole",
DlgImgAlignAbsMiddle: "Abs sredina",
DlgImgAlignBaseline	: "Bazno",
DlgImgAlignBottom	: "Dole",
DlgImgAlignMiddle	: "Sredina",
DlgImgAlignRight	: "Desno",
DlgImgAlignTextTop	: "Vrh teksta",
DlgImgAlignTop		: "Vrh",
DlgImgPreview		: "Izgled",
DlgImgAlertUrl		: "Unesite URL slike",
DlgImgLinkTab		: "Link",

// Flash Dialog
DlgFlashTitle		: "Osobine flea",
DlgFlashChkPlay		: "Automatski start",
DlgFlashChkLoop		: "Ponavljaj",
DlgFlashChkMenu		: "Ukljuci fle meni",
DlgFlashScale		: "Skaliraj",
DlgFlashScaleAll	: "Prikai sve",
DlgFlashScaleNoBorder	: "Bez ivice",
DlgFlashScaleFit	: "Popuni povrinu",

// Link Dialog
DlgLnkWindowTitle	: "Link",
DlgLnkInfoTab		: "Link Info",
DlgLnkTargetTab		: "Meta",

DlgLnkType			: "Vrsta linka",
DlgLnkTypeURL		: "URL",
DlgLnkTypeAnchor	: "Sidro na ovoj stranici",
DlgLnkTypeEMail		: "E-Mail",
DlgLnkProto			: "Protokol",
DlgLnkProtoOther	: "&lt;drugo&gt;",
DlgLnkURL			: "URL",
DlgLnkAnchorSel		: "Odaberi sidro",
DlgLnkAnchorByName	: "Po nazivu sidra",
DlgLnkAnchorById	: "Po Id-ju elementa",
DlgLnkNoAnchors		: "&lt;Nema dostupnih sidra&gt;",
DlgLnkEMail			: "E-Mail adresa",
DlgLnkEMailSubject	: "Naslov",
DlgLnkEMailBody		: "Sadraj poruke",
DlgLnkUpload		: "Poalji",
DlgLnkBtnUpload		: "Poalji na server",

DlgLnkTarget		: "Meta",
DlgLnkTargetFrame	: "&lt;okvir&gt;",
DlgLnkTargetPopup	: "&lt;popup prozor&gt;",
DlgLnkTargetBlank	: "Novi prozor (_blank)",
DlgLnkTargetParent	: "Roditeljski prozor (_parent)",
DlgLnkTargetSelf	: "Isti prozor (_self)",
DlgLnkTargetTop		: "Prozor na vrhu (_top)",
DlgLnkTargetFrameName	: "Naziv odredinog frejma",
DlgLnkPopWinName	: "Naziv popup prozora",
DlgLnkPopWinFeat	: "Mogucnosti popup prozora",
DlgLnkPopResize		: "Promenljiva velicina",
DlgLnkPopLocation	: "Lokacija",
DlgLnkPopMenu		: "Kontekstni meni",
DlgLnkPopScroll		: "Scroll bar",
DlgLnkPopStatus		: "Statusna linija",
DlgLnkPopToolbar	: "Toolbar",
DlgLnkPopFullScrn	: "Prikaz preko celog ekrana (IE)",
DlgLnkPopDependent	: "Zavisno (Netscape)",
DlgLnkPopWidth		: "irina",
DlgLnkPopHeight		: "Visina",
DlgLnkPopLeft		: "Od leve ivice ekrana (px)",
DlgLnkPopTop		: "Od vrha ekrana (px)",

DlnLnkMsgNoUrl		: "Unesite URL linka",
DlnLnkMsgNoEMail	: "Otkucajte adresu elektronske pote",
DlnLnkMsgNoAnchor	: "Odaberite sidro",

// Color Dialog
DlgColorTitle		: "Odaberite boju",
DlgColorBtnClear	: "Obrii",
DlgColorHighlight	: "Posvetli",
DlgColorSelected	: "Odaberi",

// Smiley Dialog
DlgSmileyTitle		: "Unesi smajlija",

// Special Character Dialog
DlgSpecialCharTitle	: "Odaberite specijalni karakter",

// Table Dialog
DlgTableTitle		: "Osobine tabele",
DlgTableRows		: "Redova",
DlgTableColumns		: "Kolona",
DlgTableBorder		: "Velicina okvira",
DlgTableAlign		: "Ravnanje",
DlgTableAlignNotSet	: "<nije postavljeno>",
DlgTableAlignLeft	: "Levo",
DlgTableAlignCenter	: "Sredina",
DlgTableAlignRight	: "Desno",
DlgTableWidth		: "irina",
DlgTableWidthPx		: "piksela",
DlgTableWidthPc		: "procenata",
DlgTableHeight		: "Visina",
DlgTableCellSpace	: "Celijski prostor",
DlgTableCellPad		: "Razmak celija",
DlgTableCaption		: "Naslov tabele",

// Table Cell Dialog
DlgCellTitle		: "Osobine celije",
DlgCellWidth		: "irina",
DlgCellWidthPx		: "piksela",
DlgCellWidthPc		: "procenata",
DlgCellHeight		: "Visina",
DlgCellWordWrap		: "Deljenje reci",
DlgCellWordWrapNotSet	: "<nije postavljeno>",
DlgCellWordWrapYes	: "Da",
DlgCellWordWrapNo	: "Ne",
DlgCellHorAlign		: "Vodoravno ravnanje",
DlgCellHorAlignNotSet	: "<nije postavljeno>",
DlgCellHorAlignLeft	: "Levo",
DlgCellHorAlignCenter	: "Sredina",
DlgCellHorAlignRight: "Desno",
DlgCellVerAlign		: "Vertikalno ravnanje",
DlgCellVerAlignNotSet	: "<nije postavljeno>",
DlgCellVerAlignTop	: "Gornje",
DlgCellVerAlignMiddle	: "Sredina",
DlgCellVerAlignBottom	: "Donje",
DlgCellVerAlignBaseline	: "Bazno",
DlgCellRowSpan		: "Spajanje redova",
DlgCellCollSpan		: "Spajanje kolona",
DlgCellBackColor	: "Boja pozadine",
DlgCellBorderColor	: "Boja okvira",
DlgCellBtnSelect	: "Odaberi...",

// Find Dialog
DlgFindTitle		: "Pronadi",
DlgFindFindBtn		: "Pronadi",
DlgFindNotFoundMsg	: "Traeni tekst nije pronaden.",

// Replace Dialog
DlgReplaceTitle			: "Zameni",
DlgReplaceFindLbl		: "Pronadi:",
DlgReplaceReplaceLbl	: "Zameni sa:",
DlgReplaceCaseChk		: "Razlikuj mala i velika slova",
DlgReplaceReplaceBtn	: "Zameni",
DlgReplaceReplAllBtn	: "Zameni sve",
DlgReplaceWordChk		: "Uporedi cele reci",

// Paste Operations / Dialog
PasteErrorPaste	: "Sigurnosna podeavanja Vaeg pretraivaca ne dozvoljavaju operacije automatskog lepljenja teksta. Molimo Vas da koristite precicu sa tastature (Ctrl+V).",
PasteErrorCut	: "Sigurnosna podeavanja Vaeg pretraivaca ne dozvoljavaju operacije automatskog isecanja teksta. Molimo Vas da koristite precicu sa tastature (Ctrl+X).",
PasteErrorCopy	: "Sigurnosna podeavanja Vaeg pretraivaca ne dozvoljavaju operacije automatskog kopiranja teksta. Molimo Vas da koristite precicu sa tastature (Ctrl+C).",

PasteAsText		: "Zalepi kao cist tekst",
PasteFromWord	: "Zalepi iz Worda",

DlgPasteMsg2	: "Molimo Vas da zalepite unutar donje povrine koristeci tastaturnu precicu (<STRONG>Ctrl+V</STRONG>) i da pritisnete <STRONG>OK</STRONG>.",
DlgPasteIgnoreFont		: "Ignorii Font Face definicije",
DlgPasteRemoveStyles	: "Ukloni definicije stilova",
DlgPasteCleanBox		: "Obrii sve",


// Color Picker
ColorAutomatic	: "Automatski",
ColorMoreColors	: "Vie boja...",

// Document Properties
DocProps		: "Osobine dokumenta",

// Anchor Dialog
DlgAnchorTitle		: "Osobine sidra",
DlgAnchorName		: "Ime sidra",
DlgAnchorErrorName	: "Unesite ime sidra",

// Speller Pages Dialog
DlgSpellNotInDic		: "Nije u recniku",
DlgSpellChangeTo		: "Izmeni",
DlgSpellBtnIgnore		: "Ignorii",
DlgSpellBtnIgnoreAll	: "Ignorii sve",
DlgSpellBtnReplace		: "Zameni",
DlgSpellBtnReplaceAll	: "Zameni sve",
DlgSpellBtnUndo			: "Vrati akciju",
DlgSpellNoSuggestions	: "- Bez sugestija -",
DlgSpellProgress		: "Provera spelovanja u toku...",
DlgSpellNoMispell		: "Provera spelovanja zavrena: greke nisu pronadene",
DlgSpellNoChanges		: "Provera spelovanja zavrena: Nije izmenjena nijedna rec",
DlgSpellOneChange		: "Provera spelovanja zavrena: Izmenjena je jedna rec",
DlgSpellManyChanges		: "Provera spelovanja zavrena: %1 rec(i) je izmenjeno",

IeSpellDownload			: "Provera spelovanja nije instalirana. Da li elite da je skinete sa Interneta?",

// Button Dialog
DlgButtonText	: "Tekst (vrednost)",
DlgButtonType	: "Tip",

// Checkbox and Radio Button Dialogs
DlgCheckboxName		: "Naziv",
DlgCheckboxValue	: "Vrednost",
DlgCheckboxSelected	: "Oznaceno",

// Form Dialog
DlgFormName		: "Naziv",
DlgFormAction	: "Akcija",
DlgFormMethod	: "Metoda",

// Select Field Dialog
DlgSelectName		: "Naziv",
DlgSelectValue		: "Vrednost",
DlgSelectSize		: "Velicina",
DlgSelectLines		: "linija",
DlgSelectChkMulti	: "Dozvoli viestruku selekciju",
DlgSelectOpAvail	: "Dostupne opcije",
DlgSelectOpText		: "Tekst",
DlgSelectOpValue	: "Vrednost",
DlgSelectBtnAdd		: "Dodaj",
DlgSelectBtnModify	: "Izmeni",
DlgSelectBtnUp		: "Gore",
DlgSelectBtnDown	: "Dole",
DlgSelectBtnSetValue : "Podesi kao oznacenu vrednost",
DlgSelectBtnDelete	: "Obrii",

// Textarea Dialog
DlgTextareaName	: "Naziv",
DlgTextareaCols	: "Broj kolona",
DlgTextareaRows	: "Broj redova",

// Text Field Dialog
DlgTextName			: "Naziv",
DlgTextValue		: "Vrednost",
DlgTextCharWidth	: "irina (karaktera)",
DlgTextMaxChars		: "Maksimalno karaktera",
DlgTextType			: "Tip",
DlgTextTypeText		: "Tekst",
DlgTextTypePass		: "Lozinka",

// Hidden Field Dialog
DlgHiddenName	: "Naziv",
DlgHiddenValue	: "Vrednost",

// Bulleted List Dialog
BulletedListProp	: "Osobine Bulleted liste",
NumberedListProp	: "Osobine nabrojive liste",
DlgLstType			: "Tip",
DlgLstTypeCircle	: "Krug",
DlgLstTypeDisk		: "Disk",
DlgLstTypeSquare	: "Kvadrat",
DlgLstTypeNumbers	: "Brojevi (1, 2, 3)",
DlgLstTypeLCase		: "mala slova (a, b, c)",
DlgLstTypeUCase		: "VELIKA slova (A, B, C)",
DlgLstTypeSRoman	: "Male rimske cifre (i, ii, iii)",
DlgLstTypeLRoman	: "Velike rimske cifre (I, II, III)",

// Document Properties Dialog
DlgDocGeneralTab	: "Opte osobine",
DlgDocBackTab		: "Pozadina",
DlgDocColorsTab		: "Boje i margine",
DlgDocMetaTab		: "Metapodaci",

DlgDocPageTitle		: "Naslov stranice",
DlgDocLangDir		: "Smer jezika",
DlgDocLangDirLTR	: "Sleva nadesno (LTR)",
DlgDocLangDirRTL	: "Zdesna nalevo (RTL)",
DlgDocLangCode		: "ifra jezika",
DlgDocCharSet		: "Kodiranje skupa karaktera",
DlgDocCharSetOther	: "Ostala kodiranja skupa karaktera",

DlgDocDocType		: "Zaglavlje tipa dokumenta",
DlgDocDocTypeOther	: "Ostala zaglavlja tipa dokumenta",
DlgDocIncXHTML		: "Ukljuci XHTML deklaracije",
DlgDocBgColor		: "Boja pozadine",
DlgDocBgImage		: "URL pozadinske slike",
DlgDocBgNoScroll	: "Fiksirana pozadina",
DlgDocCText			: "Tekst",
DlgDocCLink			: "Link",
DlgDocCVisited		: "Poseceni link",
DlgDocCActive		: "Aktivni link",
DlgDocMargins		: "Margine stranice",
DlgDocMaTop			: "Gornja",
DlgDocMaLeft		: "Leva",
DlgDocMaRight		: "Desna",
DlgDocMaBottom		: "Donja",
DlgDocMeIndex		: "Kljucne reci za indeksiranje dokumenta (razdvojene zarezima)",
DlgDocMeDescr		: "Opis dokumenta",
DlgDocMeAuthor		: "Autor",
DlgDocMeCopy		: "Autorska prava",
DlgDocPreview		: "Izgled stranice",

// Templates Dialog
Templates			: "Obrasci",
DlgTemplatesTitle	: "Obrasci za sadraj",
DlgTemplatesSelMsg	: "Molimo Vas da odaberete obrazac koji ce biti primenjen na stranicu (trenutni sadraj ce biti obrisan):",
DlgTemplatesLoading	: "Ucitavam listu obrazaca. Malo strpljenja...",
DlgTemplatesNoTpl	: "(Nema definisanih obrazaca)",

// About Dialog
DlgAboutAboutTab	: "O editoru",
DlgAboutBrowserInfoTab	: "Informacije o pretraivacu",
DlgAboutVersion		: "verzija",
DlgAboutLicense		: "Licencirano pod uslovima GNU Lesser General Public License",
DlgAboutInfo		: "Za vie informacija posetite"
}