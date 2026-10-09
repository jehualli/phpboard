<?
include("config.inc");
include("lib.inc");

db_connect();
sess_start();
$HITS = bump_counter();

$PAGE_TITLE = "FAQ";
include("header.inc");
?>

<FONT FACE="Arial,Helvetica" SIZE="3"><B>Frequently Asked Questions</B></FONT>
<BR><BR>

<FONT FACE="Arial,Helvetica" SIZE="2">

<B>Do I need to register?</B><BR>
No. Type your name into the form and post. The board remembers your name and
e-mail address in a cookie so you do not have to type them again.
<BR><BR>

<B>Why was my message rejected?</B><BR>
Most likely you left the name or the subject blank, or you posted twice within
30 seconds. The board makes you wait between messages so that nobody can
flood the front page.
<BR><BR>

<B>How do I make text bold?</B><BR>
Use UBB codes. HTML tags are printed as text, not as markup:
<BR>
<TABLE BORDER="1" CELLPADDING="4" CELLSPACING="0" BGCOLOR="#EEEEEE">
<TR><TD><FONT FACE="Courier New" SIZE="2">[b]like this[/b]</FONT></TD>
    <TD><FONT SIZE="2"><B>like this</B></FONT></TD></TR>
<TR><TD><FONT FACE="Courier New" SIZE="2">[i]like this[/i]</FONT></TD>
    <TD><FONT SIZE="2"><I>like this</I></FONT></TD></TR>
<TR><TD><FONT FACE="Courier New" SIZE="2">[url]http://www.yahoo.com/[/url]</FONT></TD>
    <TD><FONT SIZE="2"><A HREF="http://www.yahoo.com/">http://www.yahoo.com/</A></FONT></TD></TR>
<TR><TD><FONT FACE="Courier New" SIZE="2">[email]you@host.com[/email]</FONT></TD>
    <TD><FONT SIZE="2"><A HREF="mailto:you@host.com">you@host.com</A></FONT></TD></TR>
<TR><TD><FONT FACE="Courier New" SIZE="2">[quote]he said this[/quote]</FONT></TD>
    <TD><FONT SIZE="2"><BLOCKQUOTE><I>he said this</I></BLOCKQUOTE></FONT></TD></TR>
</TABLE>
<BR>

<B>Can I delete my own message?</B><BR>
No. Mail the webmaster and ask nicely.
<BR><BR>

<B>What is this board written in?</B><BR>
PHP 3.0 and MySQL 3.22. The whole thing is six pages and about seven hundred
lines. There is no framework, because there is not going to be one for
another four years.
<BR><BR>

<B>Is this the real 1998?</B><BR>
No. It is a reconstruction, built to show how a database-backed web
application was written in PHP 3 &#151; before sessions, before
<FONT FACE="Courier New">foreach</FONT>, before
<FONT FACE="Courier New">$_POST</FONT>, and a year before JSP existed to
compete with it.
<BR><BR>

</FONT>

<? include("footer.inc") ?>
