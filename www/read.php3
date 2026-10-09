<?
//
// read.php3 -- one message, its follow-ups, and the reply form.
//
// Laid out after Matt Wright's WWWBoard, which is what everybody's board
// looked like in 1998 whether it was written in Perl or PHP.
//
include("config.inc");
include("lib.inc");

db_connect();
sess_start();
$HITS = bump_counter();

$id = (int) gpc("id", 0);

$res = q("SELECT * FROM messages WHERE id = " . $id);
$msg = mysql_fetch_array($res);

if (!$msg) {
    $PAGE_TITLE = "Not found";
    include("header.inc");
    print("<FONT FACE=\"Arial,Helvetica\" SIZE=\"2\"><B>That message does not "
        . "exist.</B><BR>It may have been deleted by the moderator.<BR><BR>"
        . "<A HREF=\"index.php3\">Back to the index</A></FONT>\n");
    include("footer.inc");
    exit;
}

$PAGE_TITLE = $msg["subject"];
include("header.inc");
?>

<FONT FACE="Arial,Helvetica" SIZE="1">
<A HREF="index.php3">Index</A> &gt;
<? if ($msg["parent"] != 0) { ?>
  <A HREF="read.php3?id=<? print($msg["thread"]) ?>">back to the thread</A> &gt;
<? } ?>
<? print(esc(trim_to($msg["subject"], 40))) ?>
</FONT>

<BR><BR>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="4" CELLSPACING="1" BGCOLOR="#808080">
<TR BGCOLOR="#C0C0C0">
  <TD><FONT FACE="Arial,Helvetica" SIZE="3"><B><? print(esc($msg["subject"])) ?></B></FONT></TD>
</TR>
<TR BGCOLOR="#EEEEEE">
  <TD><FONT FACE="Arial,Helvetica" SIZE="1">
    Posted by
    <B><?
      if ($msg["email"] != "") {
          print("<A HREF=\"mailto:" . esc($msg["email"]) . "\">"
              . esc($msg["author"]) . "</A>");
      } else {
          print(esc($msg["author"]));
      }
    ?></B>
    <? if ($msg["homepage"] != "") { ?>
      (<A HREF="<? print(esc($msg["homepage"])) ?>" TARGET="_blank">homepage</A>)
    <? } ?>
    on <? print(fmt_date($msg["posted"])) ?>
    <? if (is_admin()) { ?>
      &nbsp;<FONT COLOR="#800000">[<? print(esc($msg["ip"])) ?>]</FONT>
      &nbsp;<A HREF="admin.php3?del=<? print($msg["id"]) ?>">[delete]</A>
    <? } ?>
  </FONT></TD>
</TR>
<TR BGCOLOR="#FFFFFF">
  <TD><FONT FACE="Arial,Helvetica" SIZE="2">
  <? print(ubb($msg["body"])) ?>
  </FONT></TD>
</TR>
</TABLE>

<BR>

<FONT FACE="Arial,Helvetica" SIZE="2"><B>Follow Ups:</B></FONT>
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
<?
// The whole thread, then just the part hanging off this message.
$kids = array();
$r2 = q("SELECT id, parent, subject, author, email, posted FROM messages "
        . "WHERE thread = " . $msg["thread"] . " AND parent <> 0 ORDER BY posted ASC");
while ($k = mysql_fetch_array($r2)) {
    if (!isset($kids[$k["parent"]])) $kids[$k["parent"]] = array();
    $kids[$k["parent"]][] = $k;
}

if (!isset($kids[$msg["id"]])) {
    print("<TR><TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\"><I>None yet.</I>"
        . "</FONT></TD></TR>\n");
} else {
    show_followups($kids, $msg["id"], 0);
}

function show_followups($kids, $parent, $depth) {
    if (!isset($kids[$parent])) return;
    if ($depth > 6) $depth = 6;
    $list = $kids[$parent];
    reset($list);
    while (list(, $m) = each($list)) {
        $w = 18 * $depth;
        $pad = "";
        if ($w > 0) {
            $pad = "<IMG SRC=\"spacer.gif\" WIDTH=\"" . $w
                 . "\" HEIGHT=\"1\" BORDER=\"0\" ALT=\"\">";
        }
        print("<TR>\n");
        print("  <TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\">" . $pad
            . "<IMG SRC=\"msg.gif\" WIDTH=\"10\" HEIGHT=\"12\" BORDER=\"0\" ALT=\"\"> "
            . "<A HREF=\"read.php3?id=" . $m["id"] . "\">"
            . esc(trim_to($m["subject"], 50)) . "</A></FONT></TD>\n");
        print("  <TD ALIGN=\"RIGHT\" NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\" "
            . "COLOR=\"#606060\">" . esc($m["author"]) . ", "
            . fmt_date($m["posted"]) . "</FONT></TD>\n");
        print("</TR>\n");
        show_followups($kids, $m["id"], $depth + 1);
    }
}
?>
</TABLE>

<BR>
<HR SIZE="1" NOSHADE>

<A NAME="reply"></A>
<FONT FACE="Arial,Helvetica" SIZE="2"><B>Post a Followup</B></FONT>

<FORM ACTION="post.php3" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="parent" VALUE="<? print($msg["id"]) ?>">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0">
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">Name:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="author" SIZE="40" MAXLENGTH="40"
             VALUE="<? print(esc(sess_get("author"))) ?>"></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">E-Mail:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="email" SIZE="40" MAXLENGTH="80"
             VALUE="<? print(esc(sess_get("email"))) ?>"></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">Subject:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="subject" SIZE="50" MAXLENGTH="100" VALUE="<?
      $s = $msg["subject"];
      if (!eregi("^re:", $s)) $s = "Re: " . $s;
      print(esc(trim_to($s, 100)));
  ?>"></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT" VALIGN="TOP"><FONT FACE="Arial,Helvetica" SIZE="2">Comments:</FONT></TD>
  <TD><TEXTAREA NAME="body" ROWS="10" COLS="50" WRAP="VIRTUAL"></TEXTAREA></TD>
</TR>
<TR>
  <TD></TD>
  <TD>
    <INPUT TYPE="SUBMIT" VALUE="Submit Followup">
    <INPUT TYPE="RESET" VALUE="Reset">
    <BR><FONT FACE="Arial,Helvetica" SIZE="1">
    You may use <B>[b]</B>bold<B>[/b]</B>, <B>[i]</B>italic<B>[/i]</B>,
    <B>[url]</B>http://...<B>[/url]</B> and <B>[quote]</B>...<B>[/quote]</B>.
    </FONT>
  </TD>
</TR>
</TABLE>
</FORM>

<? include("footer.inc") ?>
