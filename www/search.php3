<?
//
// search.php3 -- keyword search. MySQL 3.22 has no full-text index, so this
// is LIKE '%word%' and a table scan, which was fine on a board with four
// hundred messages.
//
include("config.inc");
include("lib.inc");

db_connect();
sess_start();
$HITS = bump_counter();

$kw = trim(gpc("kw", ""));
$where = (gpc("field", "author") == "text") ? "text" : "author";

$PAGE_TITLE = "Search";
include("header.inc");
?>

<FONT FACE="Arial,Helvetica" SIZE="3"><B>Search the Board</B></FONT>
<BR><BR>

<FORM ACTION="search.php3" METHOD="GET">
<TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0" BGCOLOR="#EEEEEE" WIDTH="100%">
<TR><TD>
  <FONT FACE="Arial,Helvetica" SIZE="2">
  Search for
  <INPUT TYPE="TEXT" NAME="kw" SIZE="30" MAXLENGTH="60" VALUE="<? print(esc($kw)) ?>">
  in
  <SELECT NAME="field">
    <OPTION VALUE="text"<? print($where == "text" ? " SELECTED" : "") ?>>subject and message</OPTION>
    <OPTION VALUE="author"<? print($where == "author" ? " SELECTED" : "") ?>>author name</OPTION>
  </SELECT>
  <INPUT TYPE="SUBMIT" VALUE="Search">
  </FONT>
</TD></TR>
</TABLE>
</FORM>

<?
if ($kw != "") {
    if (strlen($kw) < 3) {
        print("<FONT FACE=\"Arial,Helvetica\" SIZE=\"2\"><B>Please enter at "
            . "least three characters.</B></FONT>\n");
    } else {
        // addslashes first, then escape the LIKE wildcards the visitor typed.
        $like = addslashes($kw);
        $like = str_replace("%", "\\%", $like);
        $like = str_replace("_", "\\_", $like);

        if ($where == "author") {
            $sql = "SELECT * FROM messages WHERE author LIKE '%" . $like . "%' "
                 . "ORDER BY posted DESC LIMIT 50";
        } else {
            $sql = "SELECT * FROM messages WHERE subject LIKE '%" . $like . "%' "
                 . "OR body LIKE '%" . $like . "%' ORDER BY posted DESC LIMIT 50";
        }

        $res = q($sql);
        $n = mysql_num_rows($res);

        print("<BR><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\"><B>" . $n
            . "</B> message" . ($n == 1 ? "" : "s") . " found");
        if ($n == 50) print(" (showing the first 50)");
        print("</FONT><BR><BR>\n");

        if ($n > 0) {
            print("<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"3\" "
                . "CELLSPACING=\"1\" BGCOLOR=\"#808080\">\n");
            print("<TR BGCOLOR=\"#C0C0C0\"><TD><FONT FACE=\"Arial,Helvetica\" "
                . "SIZE=\"1\"><B>Subject</B></FONT></TD>"
                . "<TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\"><B>Author</B></FONT></TD>"
                . "<TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\"><B>Posted</B></FONT></TD></TR>\n");
            $i = 0;
            while ($m = mysql_fetch_array($res)) {
                $bg = ($i % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
                $i++;
                print("<TR BGCOLOR=\"" . $bg . "\">");
                print("<TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\">"
                    . "<A HREF=\"read.php3?id=" . $m["id"] . "\">"
                    . esc(trim_to($m["subject"], 45)) . "</A></FONT></TD>");
                print("<TD NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\">"
                    . esc($m["author"]) . "</FONT></TD>");
                print("<TD NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\">"
                    . fmt_date($m["posted"]) . "</FONT></TD>");
                print("</TR>\n");
            }
            print("</TABLE>\n");
        }
    }
}
?>

<? include("footer.inc") ?>
