<?
//
// header.inc -- the top of every page. Set $PAGE_TITLE before including.
//
// No stylesheet. CSS1 was a year old in 1998 and Netscape 4 made a mess of
// it, so layout is tables, <FONT> and BGCOLOR, like everybody else's.
//
if (!isset($PAGE_TITLE)) $PAGE_TITLE = "";
?>
<HTML>
<HEAD>
<TITLE><? print($BOARD_NAME); if ($PAGE_TITLE != "") print(" - " . esc($PAGE_TITLE)); ?></TITLE>
<META NAME="GENERATOR" CONTENT="Notepad">
<META NAME="KEYWORDS" CONTENT="message board,bbs,forum,php,discussion">
</HEAD>
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LINK="#0000EE" VLINK="#551A8B" ALINK="#FF0000">

<CENTER>
<TABLE WIDTH="620" BORDER="0" CELLPADDING="4" CELLSPACING="0">
<TR>
  <TD BGCOLOR="#000080" ALIGN="CENTER">
    <FONT FACE="Arial,Helvetica" SIZE="5" COLOR="#FFFFFF"><B><?
      print($BOARD_NAME) ?></B></FONT><BR>
    <FONT FACE="Arial,Helvetica" SIZE="1" COLOR="#C0C0C0">
      a discussion forum for me and my friends
    </FONT>
  </TD>
</TR>
<TR>
  <TD BGCOLOR="#C0C0C0" ALIGN="CENTER">
    <FONT FACE="Arial,Helvetica" SIZE="2">
    [ <A HREF="index.php3">Index</A>
    | <A HREF="post.php3">Post a Message</A>
    | <A HREF="search.php3">Search</A>
    | <A HREF="faq.php3">FAQ</A>
    | <A HREF="admin.php3"><? print(is_admin() ? "Admin Panel" : "Admin") ?></A> ]
    </FONT>
  </TD>
</TR>
</TABLE>
</CENTER>

<BR>

<CENTER>
<TABLE WIDTH="620" BORDER="0" CELLPADDING="0" CELLSPACING="0">
<TR><TD>
