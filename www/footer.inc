</TD></TR>
</TABLE>
</CENTER>

<BR>
<CENTER>
<TABLE WIDTH="620" BORDER="0" CELLPADDING="4" CELLSPACING="0">
<TR><TD ALIGN="CENTER">
  <HR WIDTH="90%" SIZE="1" NOSHADE>

  <FONT FACE="Arial,Helvetica" SIZE="1">
  You are visitor number
  </FONT>
  <BR>
  <TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" ALIGN="CENTER"><TR><TD>
  <? print(counter_html($HITS)) ?>
  </TD></TR></TABLE>
  <BR>

  <FONT FACE="Arial,Helvetica" SIZE="1">
  <? print(who_online()) ?> <?
     print(who_online() == 1 ? "person is" : "people are") ?> browsing this board
  right now.<BR>
  <BR>

  <!-- the 88x31 button collection, mandatory in 1998 -->
  <TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0" ALIGN="CENTER"><TR>
    <TD><TABLE WIDTH="88" HEIGHT="31" BORDER="1" CELLPADDING="0" CELLSPACING="0"
               BGCOLOR="#000000"><TR><TD ALIGN="CENTER">
      <FONT FACE="Arial" SIZE="1" COLOR="#FFFF00"><B>Powered by<BR>PHP 3.0</B></FONT>
    </TD></TR></TABLE></TD>
    <TD><TABLE WIDTH="88" HEIGHT="31" BORDER="1" CELLPADDING="0" CELLSPACING="0"
               BGCOLOR="#000080"><TR><TD ALIGN="CENTER">
      <FONT FACE="Arial" SIZE="1" COLOR="#FFFFFF"><B>Netscape<BR>Now!</B></FONT>
    </TD></TR></TABLE></TD>
    <TD><TABLE WIDTH="88" HEIGHT="31" BORDER="1" CELLPADDING="0" CELLSPACING="0"
               BGCOLOR="#C0C0C0"><TR><TD ALIGN="CENTER">
      <FONT FACE="Arial" SIZE="1" COLOR="#000000"><B>Made with<BR>Notepad</B></FONT>
    </TD></TR></TABLE></TD>
    <TD><TABLE WIDTH="88" HEIGHT="31" BORDER="1" CELLPADDING="0" CELLSPACING="0"
               BGCOLOR="#FFFFFF"><TR><TD ALIGN="CENTER">
      <FONT FACE="Arial" SIZE="1" COLOR="#008000"><B>Y2K<BR>COMPLIANT</B></FONT>
    </TD></TR></TABLE></TD>
  </TR></TABLE>
  <BR>

  <FONT FACE="Arial,Helvetica" SIZE="1">
  This board runs on <B>PHP 3.0</B> and <B>MySQL 3.22</B>.<BR>
  Questions? Mail the webmaster:
  <A HREF="mailto:<? print($BOARD_ADMIN) ?>"><? print($BOARD_ADMIN) ?></A><BR>
  This page is best viewed with Netscape Navigator 4 at 800x600.<BR>
  <BR>
  Copyright &copy; 1998 Dave. All rights reserved.<BR>
  <BR>
  <I>This is a reconstruction built to demonstrate how web applications were
  written in PHP 3 in 1998. Nothing here is real.</I>
  </FONT>
</TD></TR>
</TABLE>
</CENTER>

</BODY>
</HTML>
