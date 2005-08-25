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
 * File Name: cs.js
 * 	Czech language file.
 * 
 * File Authors:
 * 		David Horák (david.horak@email.cz)
 * 		Petr Plavjaník (plavjanik@gmail.com)
 */

var FCKLang =
{
// Language direction : "ltr" (left to right) or "rtl" (right to left).
Dir					: "ltr",

ToolbarCollapse		: "Skrýt panel nástrojů",
ToolbarExpand		: "Zobrazit panel nástrojů",

// Toolbar Items and Context Menu
Save				: "Uložit",
NewPage				: "Nová stránka",
Preview				: "Náhled",
Cut					: "Vyjmout",
Copy				: "Kopírovat",
Paste				: "Vložit",
PasteText			: "Vložit jako čistý text",
PasteWord			: "Vložit z Wordu",
Print				: "Tisk",
SelectAll			: "Vybrat vše",
RemoveFormat		: "Odstranit formátování",
InsertLinkLbl		: "Odkaz",
InsertLink			: "Vložit/změnit odkaz",
RemoveLink			: "Odstranit odkaz",
Anchor				: "Vložít/změnit záložku",
InsertImageLbl		: "Obrázek",
InsertImage			: "Vložit/změnit obrázek",
InsertFlashLbl		: "Flash",	//MISSING
InsertFlash			: "Insert/Edit Flash",	//MISSING
InsertTableLbl		: "Tabulka",
InsertTable			: "Vložit/změnit tabulku",
InsertLineLbl		: "Linka",
InsertLine			: "Vložit vodorovnou linku",
InsertSpecialCharLbl: "Speciální znaky",
InsertSpecialChar	: "Vložit speciální znaky",
InsertSmileyLbl		: "Smajlíky",
InsertSmiley		: "Vložit smajlík",
About				: "O aplikaci FCKeditor",
Bold				: "Tučné",
Italic				: "Kurzíva",
Underline			: "Podtržené",
StrikeThrough		: "Přeškrtnuté",
Subscript			: "Dolní index",
Superscript			: "Horní index",
LeftJustify			: "Zarovnat vlevo",
CenterJustify		: "Zarovnat na střed",
RightJustify		: "Zarovnat vpravo",
BlockJustify		: "Zarovnat do bloku",
DecreaseIndent		: "Zmenšit odsazení",
IncreaseIndent		: "Zvětšit odsazení",
Undo				: "Zpět",
Redo				: "Znovu",
NumberedListLbl		: "Číslování",
NumberedList		: "Vložit/odstranit číslovaný seznam",
BulletedListLbl		: "Odrážky",
BulletedList		: "Vložit/odstranit odrážky",
ShowTableBorders	: "Zobrzit okraje tabulek",
ShowDetails			: "Zobrazit podrobnosti",
Style				: "Styl",
FontFormat			: "Formát",
Font				: "Písmo",
FontSize			: "Velikost",
TextColor			: "Barva textu",
BGColor				: "Barva pozadí",
Source				: "Zdroj",
Find				: "Hledat",
Replace				: "Nahradit",
SpellCheck			: "Zkontrolovat pravopis",
UniversalKeyboard	: "Univerzální klávesnice",

Form			: "Formulář",
Checkbox		: "Zaškrtávací políčko",
RadioButton		: "Přepínač",
TextField		: "Textové pole",
Textarea		: "Textová oblast",
HiddenField		: "Skryté pole",
Button			: "Tlačítko",
SelectionField	: "Seznam",
ImageButton		: "Obrázkové tlačítko",

// Context Menu
EditLink			: "Změnit odkaz",
InsertRow			: "Vložit řádek",
DeleteRows			: "Smazat řádek",
InsertColumn		: "Vložit sloupec",
DeleteColumns		: "Smazat sloupec",
InsertCell			: "Vložit buňku",
DeleteCells			: "Smazat buňky",
MergeCells			: "Sloučit buňky",
SplitCell			: "Rozdělit buňku",
CellProperties		: "Vlastnosti buňky",
TableProperties		: "Vlastnosti tabulky",
ImageProperties		: "Vlastnosti obrázku",
FlashProperties		: "Flash Properties",	//MISSING

AnchorProp			: "Vlastnosti záložky",
ButtonProp			: "Vlastnosti tlačítka",
CheckboxProp		: "Vlastnosti zaškrtávacího políčka",
HiddenFieldProp		: "Vlastnosti skrytého pole",
RadioButtonProp		: "Vlastnosti přepínače",
ImageButtonProp		: "Vlastností obrázkového tlačítka",
TextFieldProp		: "Vlastnosti textového pole",
SelectionFieldProp	: "Vlastnosti seznamu",
TextareaProp		: "Vlastnosti textové oblasti",
FormProp			: "Vlastnosti formuláře",

FontFormats			: "Normální;Formátovaný;Adresa;Nadpis 1;Nadpis 2;Nadpis 3;Nadpis 4;Nadpis 5;Nadpis 6",

// Alerts and Messages
ProcessingXHTML		: "Probíhá zpracování XHTML. Prosím čekejte...",
Done				: "Hotovo",
PasteWordConfirm	: "Jak je vidět, vkládaný text je kopírován z Wordu. Chceet jej před vložením vyčistit?",
NotCompatiblePaste	: "Tento příkaz je dostupný pouze v Internet Exploreru verze 5.5 nebo vyšší. Chcete vložit text bez vyčištění?",
UnknownToolbarItem	: "Neznámá položka panelu nástrojů \"%1\"",
UnknownCommand		: "Neznámý příkaz \"%1\"",
NotImplemented		: "Příkaz není implementován",
UnknownToolbarSet	: "Panel nástrojů \"%1\" neexistuje",

// Dialogs
DlgBtnOK			: "OK",
DlgBtnCancel		: "Storno",
DlgBtnClose			: "Zavřít",
DlgBtnBrowseServer	: "Vybrat na serveru",
DlgAdvancedTag		: "Rozšířené",
DlgOpOther			: "&lt;Ostatní&gt;",
DlgInfoTab			: "Info",	//MISSING
DlgAlertUrl			: "Please insert the URL",	//MISSING

// General Dialogs Labels
DlgGenNotSet		: "&lt;nenastaveno&gt;",
DlgGenId			: "Id",
DlgGenLangDir		: "Orientace jazyka",
DlgGenLangDirLtr	: "Zleva do prava (LTR)",
DlgGenLangDirRtl	: "Zprava do leva (RTL)",
DlgGenLangCode		: "Kód jazyka",
DlgGenAccessKey		: "Přístupový klíč",
DlgGenName			: "Jméno",
DlgGenTabIndex		: "Pořadí prvku",
DlgGenLongDescr		: "Dlouhý popis URL",
DlgGenClass			: "Třída stylu",
DlgGenTitle			: "Pomocný titulek",
DlgGenContType		: "Pomocný typ obsahu",
DlgGenLinkCharset	: "Přiřazená znaková sada",
DlgGenStyle			: "Styl",

// Image Dialog
DlgImgTitle			: "Vlastosti obrázku",
DlgImgInfoTab		: "Informace o obrázku",
DlgImgBtnUpload		: "Odeslat na server",
DlgImgURL			: "URL",
DlgImgUpload		: "Odeslat",
DlgImgAlt			: "Alternativní text",
DlgImgWidth			: "Šířka",
DlgImgHeight		: "Výška",
DlgImgLockRatio		: "Zámek",
DlgBtnResetSize		: "Původní velikost",
DlgImgBorder		: "Okraje",
DlgImgHSpace		: "H-mezera",
DlgImgVSpace		: "V-mezera",
DlgImgAlign			: "Zarovnání",
DlgImgAlignLeft		: "Vlevo",
DlgImgAlignAbsBottom: "Zcela dolů",
DlgImgAlignAbsMiddle: "Doprostřed",
DlgImgAlignBaseline	: "Na účaří",
DlgImgAlignBottom	: "Dolů",
DlgImgAlignMiddle	: "Na střed",
DlgImgAlignRight	: "Vpravo",
DlgImgAlignTextTop	: "Na horní okraj textu",
DlgImgAlignTop		: "Nahoru",
DlgImgPreview		: "Náhled",
DlgImgAlertUrl		: "Zadejte prosím URL obrázku",
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
DlgLnkWindowTitle	: "Odkaz",
DlgLnkInfoTab		: "Informace o odkazu",
DlgLnkTargetTab		: "Cíl",

DlgLnkType			: "Typ odkazu",
DlgLnkTypeURL		: "URL",
DlgLnkTypeAnchor	: "Kotva v této stránce",
DlgLnkTypeEMail		: "E-Mail",
DlgLnkProto			: "Protokol",
DlgLnkProtoOther	: "&lt;jiný&gt;",
DlgLnkURL			: "URL",
DlgLnkAnchorSel		: "Vybrat kotvu",
DlgLnkAnchorByName	: "Podle jména kotvy",
DlgLnkAnchorById	: "Podle Id objektu",
DlgLnkNoAnchors		: "&lt;Ve stránce žádná kotva není definována&gt;",
DlgLnkEMail			: "E-Mailová adresa",
DlgLnkEMailSubject	: "Předmět zprávy",
DlgLnkEMailBody		: "Tělo zprávy",
DlgLnkUpload		: "Odeslat",
DlgLnkBtnUpload		: "Odeslat na Server",

DlgLnkTarget		: "Cíl",
DlgLnkTargetFrame	: "&lt;rámec&gt;",
DlgLnkTargetPopup	: "&lt;vyskakovací okno&gt;",
DlgLnkTargetBlank	: "Nové okno (_blank)",
DlgLnkTargetParent	: "Rodičovské okno (_parent)",
DlgLnkTargetSelf	: "Stejné okno (_self)",
DlgLnkTargetTop		: "Hlavní okno (_top)",
DlgLnkTargetFrameName	: "Název cílového rámu",
DlgLnkPopWinName	: "Název vyskakovacího okna",
DlgLnkPopWinFeat	: "Vlastnosti vyskakovacího okna",
DlgLnkPopResize		: "Měnitelná velikost",
DlgLnkPopLocation	: "Panel umístění",
DlgLnkPopMenu		: "Panel nabídky",
DlgLnkPopScroll		: "Posuvníky",
DlgLnkPopStatus		: "Stavový řádek",
DlgLnkPopToolbar	: "Panel nástrojů",
DlgLnkPopFullScrn	: "Celá obrazovka (IE)",
DlgLnkPopDependent	: "Závislost (Netscape)",
DlgLnkPopWidth		: "Šířka",
DlgLnkPopHeight		: "Výška",
DlgLnkPopLeft		: "Levý okraj",
DlgLnkPopTop		: "Horní okraj",

DlnLnkMsgNoUrl		: "Zadejte prosím URL odkazu",
DlnLnkMsgNoEMail	: "Zadejte prosím e-mailovou adresu",
DlnLnkMsgNoAnchor	: "Vyberte prosím kotvu",

// Color Dialog
DlgColorTitle		: "Výběr barvy",
DlgColorBtnClear	: "Vymazat",
DlgColorHighlight	: "Zvýrazněná",
DlgColorSelected	: "Vybraná",

// Smiley Dialog
DlgSmileyTitle		: "Vkládání smajlíků",

// Special Character Dialog
DlgSpecialCharTitle	: "Výběr speciálního znaku",

// Table Dialog
DlgTableTitle		: "Vlastnosti tabulky",
DlgTableRows		: "Řádky",
DlgTableColumns		: "Sloupce",
DlgTableBorder		: "Ohraničení",
DlgTableAlign		: "Zarovnání",
DlgTableAlignNotSet	: "<nenastaveno>",
DlgTableAlignLeft	: "Vlevo",
DlgTableAlignCenter	: "Na střed",
DlgTableAlignRight	: "Vpravo",
DlgTableWidth		: "Šířka",
DlgTableWidthPx		: "bodů",
DlgTableWidthPc		: "procent",
DlgTableHeight		: "Výška",
DlgTableCellSpace	: "Vzdálenost buněk",
DlgTableCellPad		: "Odsazení obsahu",
DlgTableCaption		: "Popis",

// Table Cell Dialog
DlgCellTitle		: "Vlastnosti buňky",
DlgCellWidth		: "Šířka",
DlgCellWidthPx		: "bodů",
DlgCellWidthPc		: "procent",
DlgCellHeight		: "Výška",
DlgCellWordWrap		: "Zalamování",
DlgCellWordWrapNotSet	: "<nenanstaveno>",
DlgCellWordWrapYes	: "Ano",
DlgCellWordWrapNo	: "Ne",
DlgCellHorAlign		: "Vodorovné zarovnání",
DlgCellHorAlignNotSet	: "<nenastaveno>",
DlgCellHorAlignLeft	: "Vlevo",
DlgCellHorAlignCenter	: "Na střed",
DlgCellHorAlignRight: "Vpravo",
DlgCellVerAlign		: "Svislé zarovnání",
DlgCellVerAlignNotSet	: "<nenastaveno>",
DlgCellVerAlignTop	: "Nahoru",
DlgCellVerAlignMiddle	: "Doprostřed",
DlgCellVerAlignBottom	: "Dolů",
DlgCellVerAlignBaseline	: "Na účaří",
DlgCellRowSpan		: "Sloučené řádky",
DlgCellCollSpan		: "Sloučené sloupce",
DlgCellBackColor	: "Barva pozadí",
DlgCellBorderColor	: "Rarva ohraničení",
DlgCellBtnSelect	: "Výběr...",

// Find Dialog
DlgFindTitle		: "Hledat",
DlgFindFindBtn		: "Hledat",
DlgFindNotFoundMsg	: "Hledaný text nebyl nalezen.",

// Replace Dialog
DlgReplaceTitle			: "Nahradit",
DlgReplaceFindLbl		: "Co hledat:",
DlgReplaceReplaceLbl	: "Čím nahradit:",
DlgReplaceCaseChk		: "Rozlišovat velikost písma",
DlgReplaceReplaceBtn	: "Nahradit",
DlgReplaceReplAllBtn	: "Nahradit vše",
DlgReplaceWordChk		: "Pouze celá slova",

// Paste Operations / Dialog
PasteErrorPaste	: "Bezpečnostní nastavení Vašeho prohlížeče nedovolují editoru spustit funkci pro vložení textu ze schránky. Prosím vložte text ze schránky pomocí klávesnice (Ctrl+V).",
PasteErrorCut	: "Bezpečnostní nastavení Vašeho prohlížeče nedovolují editoru spustit funkci pro vyjmutí zvoleného textu do schránky. Prosím vyjměte zvolený text do schránky pomocí klávesnice (Ctrl+X).",
PasteErrorCopy	: "Bezpečnostní nastavení Vašeho prohlížeče nedovolují editoru spustit funkci pro kopírování zvoleného textu do schránky. Prosím zkopírujte zvolený text do schránky pomocí klávesnice (Ctrl+C).",

PasteAsText		: "Vložit jako čistý text",
PasteFromWord	: "Vložit text z Wordu",

DlgPasteMsg2	: "Please paste inside the following box using the keyboard (<STRONG>Ctrl+V</STRONG>) and hit <STRONG>OK</STRONG>.",	//MISSING
DlgPasteIgnoreFont		: "Ignore Font Face definitions",	//MISSING
DlgPasteRemoveStyles	: "Remove Styles definitions",	//MISSING
DlgPasteCleanBox		: "Clean Up Box",	//MISSING


// Color Picker
ColorAutomatic	: "Automaticky",
ColorMoreColors	: "Více barev...",

// Document Properties
DocProps		: "Vlastnosti dokumentu",

// Anchor Dialog
DlgAnchorTitle		: "Vlastnosti záložky",
DlgAnchorName		: "Název záložky",
DlgAnchorErrorName	: "Zadejte prosím název záložky",

// Speller Pages Dialog
DlgSpellNotInDic		: "Není ve slovníku",
DlgSpellChangeTo		: "Změnit na",
DlgSpellBtnIgnore		: "Přeskočit",
DlgSpellBtnIgnoreAll	: "Přeskakovat vše",
DlgSpellBtnReplace		: "Zaměnit",
DlgSpellBtnReplaceAll	: "Zaměňovat vše",
DlgSpellBtnUndo			: "Zpět",
DlgSpellNoSuggestions	: "- žádné návrhy -",
DlgSpellProgress		: "Probíhá kontrola pravopisu...",
DlgSpellNoMispell		: "Kontrola pravopisu dokončena: Žádné pravopisné chyby nenalezeny",
DlgSpellNoChanges		: "Kontrola pravopisu dokončena: Beze změn",
DlgSpellOneChange		: "Kontrola pravopisu dokončena: Jedno slovo změněno",
DlgSpellManyChanges		: "Kontrola pravopisu dokončena: %1 slov změněno",

IeSpellDownload			: "Kontrola pravopisu není nainstalována. Chcete ji nyní stáhnout?",

// Button Dialog
DlgButtonText	: "Popisek",
DlgButtonType	: "Typ",

// Checkbox and Radio Button Dialogs
DlgCheckboxName		: "Název",
DlgCheckboxValue	: "Hodnota",
DlgCheckboxSelected	: "Zaškrtnuto",

// Form Dialog
DlgFormName		: "Název",
DlgFormAction	: "Akce",
DlgFormMethod	: "Metoda",

// Select Field Dialog
DlgSelectName		: "Název",
DlgSelectValue		: "Hodnota",
DlgSelectSize		: "Velikost",
DlgSelectLines		: "řádků",
DlgSelectChkMulti	: "Povolit mnohonásobné výběry",
DlgSelectOpAvail	: "Dostupná nastavení",
DlgSelectOpText		: "Text",
DlgSelectOpValue	: "Hodnota",
DlgSelectBtnAdd		: "Přidat",
DlgSelectBtnModify	: "Změnit",
DlgSelectBtnUp		: "Nahoru",
DlgSelectBtnDown	: "Dolů",
DlgSelectBtnSetValue : "Nastavit jako vybranou hodnotu",
DlgSelectBtnDelete	: "Smazat",

// Textarea Dialog
DlgTextareaName	: "Název",
DlgTextareaCols	: "Sloupců",
DlgTextareaRows	: "Řádků",

// Text Field Dialog
DlgTextName			: "Název",
DlgTextValue		: "Hodnota",
DlgTextCharWidth	: "Šířka ve znacích",
DlgTextMaxChars		: "Maximální počet znaků",
DlgTextType			: "Typ",
DlgTextTypeText		: "Text",
DlgTextTypePass		: "Heslo",

// Hidden Field Dialog
DlgHiddenName	: "Název",
DlgHiddenValue	: "Hodnota",

// Bulleted List Dialog
BulletedListProp	: "Vlastnosti odrážek",
NumberedListProp	: "Vlastnosti číslovaného seznamu",
DlgLstType			: "Typ",
DlgLstTypeCircle	: "Kružnice",
DlgLstTypeDisk		: "Plný kruh",
DlgLstTypeSquare	: "Čtverec",
DlgLstTypeNumbers	: "Čísla (1, 2, 3)",
DlgLstTypeLCase		: "Malá písmena (a, b, c)",
DlgLstTypeUCase		: "Velká písmena (A, B, C)",
DlgLstTypeSRoman	: "Malé římská číslice (i, ii, iii)",
DlgLstTypeLRoman	: "Velké římské číslice (I, II, III)",

// Document Properties Dialog
DlgDocGeneralTab	: "Obecné",
DlgDocBackTab		: "Pozadí",
DlgDocColorsTab		: "Barvy a okraje",
DlgDocMetaTab		: "Metadata",

DlgDocPageTitle		: "Titulek stránky",
DlgDocLangDir		: "Směr jazyku",
DlgDocLangDirLTR	: "Zleva do prava ",
DlgDocLangDirRTL	: "Zprava doleva",
DlgDocLangCode		: "Kód jazyku",
DlgDocCharSet		: "Znaková sada",
DlgDocCharSetOther	: "Další znaková sada",

DlgDocDocType		: "Typ dokumentu",
DlgDocDocTypeOther	: "Jiný typ dokumetu",
DlgDocIncXHTML		: "Zahrnou deklarace XHTML",
DlgDocBgColor		: "Barva pozadí",
DlgDocBgImage		: "URL obrázku na pozadí",
DlgDocBgNoScroll	: "Nerolovatelné pozadí",
DlgDocCText			: "Text",
DlgDocCLink			: "Odkaz",
DlgDocCVisited		: "Navštívený odkaz",
DlgDocCActive		: "Vybraný odkaz",
DlgDocMargins		: "Okraje stránky",
DlgDocMaTop			: "Horní",
DlgDocMaLeft		: "Levý",
DlgDocMaRight		: "Pravý",
DlgDocMaBottom		: "Dolní",
DlgDocMeIndex		: "Klíčová slova (oddělená čárkou)",
DlgDocMeDescr		: "Popis dokumentu",
DlgDocMeAuthor		: "Autor",
DlgDocMeCopy		: "Autorská práva",
DlgDocPreview		: "Náhled",

// Templates Dialog
Templates			: "Templates",	//MISSING
DlgTemplatesTitle	: "Content Templates",	//MISSING
DlgTemplatesSelMsg	: "Please select the template to open in the editor<br>(the actual contents will be lost):",	//MISSING
DlgTemplatesLoading	: "Loading templates list. Please wait...",	//MISSING
DlgTemplatesNoTpl	: "(No templates defined)",	//MISSING

// About Dialog
DlgAboutAboutTab	: "O aplikaci",
DlgAboutBrowserInfoTab	: "Informace o prohlížeči",
DlgAboutVersion		: "verze",
DlgAboutLicense		: "Licencováno pomocí GNU Lesser General Public License",
DlgAboutInfo		: "Více informací získáte na"
}