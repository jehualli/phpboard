<?
//
// admin.php3 -- moderator login and tools.
//
// The session is the one in lib.inc: an id in a cookie and a serialized
// array in a table, because session_start() will not exist until PHP 4.
//
include("config.inc");
include("lib.inc");

db_connect();
sess_start();
$HITS = bump_counter();

$notice = "";
$error = "";

// ---- log in / out ----

if ($REQUEST_METHOD == "POST" && gpc_isset("pass")) {
    if (md5(gpc("pass", "")) == $ADMIN_PASS) {
        sess_set("admin", "1");
        $notice = "You are logged in as the moderator.";
    } else {
        $error = "Wrong password.";
        // Slow down anybody working through a dictionary.
        sleep(2);
    }
}

if (gpc_isset("logout")) {
    sess_set("admin", "0");
    $notice = "You have been logged out.";
}

// ---- moderator actions ----

if (is_admin() && gpc_isset("del")) {
    $del = (int) gpc("del", 0);
    $res = q("SELECT id, parent, thread, subject FROM messages WHERE id = " . $del);
    if ($m = mysql_fetch_array($res)) {
        if ($m["parent"] == 0) {
            // Deleting the root takes the whole thread with it.
            $r2 = q("SELECT COUNT(*) AS n FROM messages WHERE thread = " . $m["thread"]);
            $c = mysql_fetch_array($r2);
            q("DELETE FROM messages WHERE thread = " . $m["thread"]);
            $notice = "Deleted the thread \"" . esc($m["subject"]) . "\" and "
                    . ($c["n"] - 1) . " follow-up(s).";
        } else {
            // Orphaned replies are re-parented rather than lost.
            q("UPDATE messages SET parent = " . $m["parent"]
              . " WHERE parent = " . $m["id"]);
            q("DELETE FROM messages WHERE id = " . $m["id"]);
            $notice = "Deleted \"" . esc($m["subject"]) . "\". Its follow-ups "
                    . "were moved up one level.";
        }
    }
}

if (is_admin() && $REQUEST_METHOD == "POST" && gpc_isset("announce")) {
    q("UPDATE announce SET body = " . sqlstr(trim(gpc("announce", "")))
      . " WHERE id = 1");
    $notice = "Announcement updated.";
}

$PAGE_TITLE = "Admin";
include("header.inc");
?>

<FONT FACE="Arial,Helvetica" SIZE="3"><B>Moderator</B></FONT><BR><BR>

<?
if ($notice != "") {
    print("<TABLE WIDTH=\"100%\" BORDER=\"1\" CELLPADDING=\"6\" CELLSPACING=\"0\" "
        . "BGCOLOR=\"#EEFFEE\"><TR><TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\">"
        . $notice . "</FONT></TD></TR></TABLE><BR>\n");
}
if ($error != "") {
    print("<TABLE WIDTH=\"100%\" BORDER=\"1\" CELLPADDING=\"6\" CELLSPACING=\"0\" "
        . "BGCOLOR=\"#FFEEEE\"><TR><TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\" "
        . "COLOR=\"#CC0000\">" . esc($error) . "</FONT></TD></TR></TABLE><BR>\n");
}

if (!is_admin()) {
?>

<FORM ACTION="admin.php3" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0" BGCOLOR="#EEEEEE">
<TR><TD><FONT FACE="Arial,Helvetica" SIZE="2">Moderator password:</FONT></TD>
    <TD><INPUT TYPE="PASSWORD" NAME="pass" SIZE="20">
        <INPUT TYPE="SUBMIT" VALUE="Log In"></TD></TR>
</TABLE>
</FORM>

<FONT FACE="Arial,Helvetica" SIZE="1">
This area is for the webmaster. If you have found a message that breaks the
rules, mail <A HREF="mailto:<? print($BOARD_ADMIN) ?>"><? print($BOARD_ADMIN) ?></A>
instead of trying to guess the password.<BR>
<I>(For this demonstration the password is <B>letmein</B>.)</I>
</FONT>

<?
} else {
    $res = q("SELECT COUNT(*) AS n FROM messages");
    $row = mysql_fetch_array($res);
    $nmsg = $row["n"];
    $res = q("SELECT COUNT(*) AS n FROM messages WHERE parent = 0");
    $row = mysql_fetch_array($res);
    $nthread = $row["n"];
    $res = q("SELECT COUNT(*) AS n FROM sessions");
    $row = mysql_fetch_array($res);
    $nsess = $row["n"];
?>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="4" CELLSPACING="1" BGCOLOR="#808080">
<TR BGCOLOR="#C0C0C0"><TD COLSPAN="2"><FONT FACE="Arial,Helvetica" SIZE="2">
  <B>Board statistics</B></FONT></TD></TR>
<TR BGCOLOR="#FFFFFF"><TD><FONT SIZE="2">Threads</TD><TD><FONT SIZE="2"><B><? print($nthread) ?></B></TD></TR>
<TR BGCOLOR="#FFFFFF"><TD><FONT SIZE="2">Messages</TD><TD><FONT SIZE="2"><B><? print($nmsg) ?></B></TD></TR>
<TR BGCOLOR="#FFFFFF"><TD><FONT SIZE="2">Page views</TD><TD><FONT SIZE="2"><B><? print($HITS) ?></B></TD></TR>
<TR BGCOLOR="#FFFFFF"><TD><FONT SIZE="2">Live sessions</TD><TD><FONT SIZE="2"><B><? print($nsess) ?></B></TD></TR>
</TABLE>

<BR>

<FORM ACTION="admin.php3" METHOD="POST">
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="4" CELLSPACING="1" BGCOLOR="#808080">
<TR BGCOLOR="#C0C0C0"><TD><FONT FACE="Arial,Helvetica" SIZE="2">
  <B>Announcement</B> (shown at the top of the index; leave blank to remove)</FONT></TD></TR>
<TR BGCOLOR="#FFFFFF"><TD>
  <?
  $res = q("SELECT body FROM announce WHERE id = 1");
  $a = mysql_fetch_array($res);
  ?>
  <TEXTAREA NAME="announce" ROWS="3" COLS="60" WRAP="VIRTUAL"><? print(esc($a["body"])) ?></TEXTAREA>
  <BR><INPUT TYPE="SUBMIT" VALUE="Save Announcement">
</TD></TR>
</TABLE>
</FORM>

<BR>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="4" CELLSPACING="1" BGCOLOR="#808080">
<TR BGCOLOR="#C0C0C0"><TD COLSPAN="4"><FONT FACE="Arial,Helvetica" SIZE="2">
  <B>Last 25 messages</B></FONT></TD></TR>
<?
$res = q("SELECT * FROM messages ORDER BY posted DESC LIMIT 25");
$i = 0;
while ($m = mysql_fetch_array($res)) {
    $bg = ($i % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
    $i++;
    print("<TR BGCOLOR=\"" . $bg . "\">");
    print("<TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\"><A HREF=\"read.php3?id="
        . $m["id"] . "\">" . esc(trim_to($m["subject"], 38)) . "</A>");
    if ($m["parent"] == 0) print(" <FONT SIZE=\"1\" COLOR=\"#800000\">[thread]</FONT>");
    print("</FONT></TD>");
    print("<TD NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\">" . esc($m["author"]) . "</FONT></TD>");
    print("<TD NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\">" . esc($m["ip"]) . "</FONT></TD>");
    print("<TD NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\">"
        . "<A HREF=\"admin.php3?del=" . $m["id"] . "\" "
        . "ONCLICK=\"return confirm('Delete this message?')\">delete</A></FONT></TD>");
    print("</TR>\n");
}
?>
</TABLE>

<BR>
<FONT FACE="Arial,Helvetica" SIZE="2">
<A HREF="admin.php3?logout=1">Log out</A>
</FONT>

<? } ?>

<? include("footer.inc") ?>
