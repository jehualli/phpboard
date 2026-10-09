<?
//
// index.php3 -- the board index. Threads newest first, replies nested
// underneath.
//
// This is the whole application design: one file per page, the SQL right
// there in the middle of the HTML, and no layer between them. Nobody called
// it Model 1 until there was a Model 2 to tell it apart from.
//
include("config.inc");
include("lib.inc");

db_connect();
sess_start();
$HITS = bump_counter();

$page = (int) gpc("p", 1);
if ($page < 1) $page = 1;

// How many threads are there? MySQL 3.22 has no subqueries, so everything
// here is a separate trip to the server and a loop in PHP.
$res = q("SELECT COUNT(*) AS n FROM messages WHERE parent = 0");
$row = mysql_fetch_array($res);
$total = $row["n"];
$pages = (int) (($total + $PER_PAGE - 1) / $PER_PAGE);
if ($pages < 1) $pages = 1;
if ($page > $pages) $page = $pages;
$start = ($page - 1) * $PER_PAGE;

$PAGE_TITLE = "";
include("header.inc");

$res = q("SELECT * FROM announce WHERE id = 1");
if ($row = mysql_fetch_array($res)) {
    if (trim($row["body"]) != "") {
?>
<TABLE WIDTH="100%" BORDER="1" CELLPADDING="6" CELLSPACING="0" BGCOLOR="#FFFFCC">
<TR><TD>
  <FONT FACE="Arial,Helvetica" SIZE="2">
  <FONT COLOR="#FF0000"><B>*** ANNOUNCEMENT ***</B></FONT><BR>
  <? print(ubb($row["body"])) ?>
  </FONT>
</TD></TR>
</TABLE>
<BR>
<?
    }
}
?>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="0" CELLSPACING="0">
<TR>
  <TD><FONT FACE="Arial,Helvetica" SIZE="2"><B><? print($total) ?></B>
      thread<? print($total == 1 ? "" : "s") ?> on this board</FONT></TD>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">
      <A HREF="post.php3"><B>Post a new message</B></A></FONT></TD>
</TR>
</TABLE>

<HR SIZE="1" NOSHADE>

<?
$res = q("SELECT * FROM messages WHERE parent = 0 ORDER BY bumped DESC "
         . "LIMIT " . $start . ", " . $PER_PAGE);

if (mysql_num_rows($res) == 0) {
    print("<FONT FACE=\"Arial,Helvetica\" SIZE=\"2\"><I>There are no messages "
        . "on this board yet. Be the first!</I></FONT><BR>\n");
}

while ($thread = mysql_fetch_array($res)) {

    // Pull every reply in this thread in one go, then build the tree in PHP.
    // One query per thread; on a busy board you noticed.
    $kids = array();
    $r2 = q("SELECT id, parent, subject, author, email, posted FROM messages "
            . "WHERE thread = " . $thread["id"] . " AND parent <> 0 "
            . "ORDER BY posted ASC");
    while ($k = mysql_fetch_array($r2)) {
        if (!isset($kids[$k["parent"]])) $kids[$k["parent"]] = array();
        $kids[$k["parent"]][] = $k;
    }

    print("<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\">\n");
    show_message_line($thread, 0);
    show_replies($kids, $thread["id"], 1);
    print("</TABLE>\n");
    print("<HR SIZE=\"1\" NOSHADE>\n");
}

//
// One line of the thread tree. Depth is turned into spacer cells, which is
// how you indented anything before CSS margins were dependable.
//
function show_message_line($m, $depth) {
    // Indent with a stretched single-pixel GIF inside the cell. Putting the
    // spacer in a column of its own does not work: table columns are shared,
    // so the widest row would indent every other row with it.
    $w = 18 * $depth;
    print("<TR>\n");
    print("  <TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\">");
    if ($w > 0) {
        print("<IMG SRC=\"spacer.gif\" WIDTH=\"" . $w
            . "\" HEIGHT=\"1\" BORDER=\"0\" ALT=\"\">");
    }
    if ($depth == 0) {
        print("<IMG SRC=\"folder.gif\" WIDTH=\"15\" HEIGHT=\"13\" BORDER=\"0\" ALT=\"\"> ");
        print("<A HREF=\"read.php3?id=" . $m["id"] . "\"><B>"
            . esc($m["subject"]) . "</B></A>");
    } else {
        print("<IMG SRC=\"msg.gif\" WIDTH=\"10\" HEIGHT=\"12\" BORDER=\"0\" ALT=\"\"> ");
        print("<A HREF=\"read.php3?id=" . $m["id"] . "\">"
            . esc(trim_to($m["subject"], 52)) . "</A>");
    }
    print("</FONT></TD>\n");
    print("  <TD ALIGN=\"RIGHT\" NOWRAP><FONT FACE=\"Arial,Helvetica\" SIZE=\"1\" "
        . "COLOR=\"#606060\">");
    if ($m["email"] != "") {
        print("<A HREF=\"mailto:" . esc($m["email"]) . "\">" . esc($m["author"]) . "</A>");
    } else {
        print(esc($m["author"]));
    }
    print(", " . fmt_date($m["posted"]));
    print("</FONT></TD>\n");
    print("</TR>\n");
}

//
// Recursion, because a threaded board is a tree and 1998 had no other trick
// for drawing one.
//
function show_replies($kids, $parent, $depth) {
    if (!isset($kids[$parent])) return;
    if ($depth > 6) $depth = 6;
    $list = $kids[$parent];
    reset($list);
    while (list(, $m) = each($list)) {
        show_message_line($m, $depth);
        show_replies($kids, $m["id"], $depth + 1);
    }
}
?>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="0" CELLSPACING="0">
<TR>
  <TD><FONT FACE="Arial,Helvetica" SIZE="2">
  Page <? print($page) ?> of <? print($pages) ?>
  </FONT></TD>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">
  <?
  if ($page > 1) print("<A HREF=\"index.php3?p=" . ($page - 1) . "\">&lt;&lt; Previous</A> ");
  for ($i = 1; $i <= $pages; $i++) {
      if ($i == $page) print("<B>[" . $i . "]</B> ");
      else print("<A HREF=\"index.php3?p=" . $i . "\">" . $i . "</A> ");
  }
  if ($page < $pages) print("<A HREF=\"index.php3?p=" . ($page + 1) . "\">Next &gt;&gt;</A>");
  ?>
  </FONT></TD>
</TR>
</TABLE>

<? include("footer.inc") ?>
