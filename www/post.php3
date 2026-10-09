<?
//
// post.php3 -- new threads and follow-ups. The form and the code that
// handles it live in the same file, because in 1998 they always did.
//
include("config.inc");
include("lib.inc");

db_connect();
sess_start();
$HITS = bump_counter();

$parent   = (int) gpc("parent", 0);
$author   = trim(gpc("author", ""));
$email    = trim(gpc("email", ""));
$homepage = trim(gpc("homepage", ""));
$subject  = trim(gpc("subject", ""));
$body     = trim(gpc("body", ""));
$errors   = array();

// Remember who you are, so the next post fills itself in. This is what a
// board did before it had accounts.
if ($author != "") sess_set("author", $author);
if ($email != "")  sess_set("email", $email);

if ($author == "" && sess_get("author") != "") $author = sess_get("author");
if ($email == ""  && sess_get("email")  != "") $email  = sess_get("email");

$posted_ok = 0;

if ($REQUEST_METHOD == "POST") {

    if ($author == "")  $errors[] = "You must enter your name.";
    if ($subject == "") $errors[] = "You must enter a subject.";
    if ($body == "")    $errors[] = "You forgot to type a message.";
    if (strlen($subject) > 100) $errors[] = "Subject is too long (100 characters max).";
    if (strlen($body) > 8000)   $errors[] = "Message is too long (8000 characters max).";

    if ($email != "" && !eregi("^[a-z0-9._-]+@[a-z0-9.-]+\\.[a-z]{2,4}$", $email)) {
        $errors[] = "That does not look like an e-mail address. Leave it blank "
                  . "if you would rather not say.";
    }
    if ($homepage != "" && !eregi("^http://", $homepage)) {
        $homepage = "http://" . $homepage;
    }

    // Parent must exist, or the thread tree breaks.
    $thread = 0;
    if ($parent != 0) {
        $res = q("SELECT id, thread FROM messages WHERE id = " . $parent);
        $prow = mysql_fetch_array($res);
        if (!$prow) {
            $errors[] = "The message you are replying to no longer exists.";
            $parent = 0;
        } else {
            $thread = $prow["thread"];
        }
    }

    // Flood control. Every board got one after its first bad weekend.
    $res = q("SELECT MAX(posted) AS last FROM messages WHERE ip = "
             . sqlstr($REMOTE_ADDR));
    $frow = mysql_fetch_array($res);
    if ($frow["last"] != "" && (time() - $frow["last"]) < $FLOOD_SECS) {
        $wait = $FLOOD_SECS - (time() - $frow["last"]);
        $errors[] = "You are posting too fast. Please wait " . $wait
                  . " more seconds and try again.";
    }

    if (count($errors) == 0) {
        $now = time();
        q("INSERT INTO messages (parent, thread, subject, author, email, "
          . "homepage, body, posted, bumped, ip) VALUES ("
          . $parent . ", 0, "
          . sqlstr($subject) . ", "
          . sqlstr($author) . ", "
          . sqlstr($email) . ", "
          . sqlstr($homepage) . ", "
          . sqlstr($body) . ", "
          . $now . ", " . $now . ", "
          . sqlstr($REMOTE_ADDR) . ")");

        $new = mysql_insert_id();

        // A new thread is its own root. A reply belongs to its root and
        // pushes that root back to the top of the index.
        if ($parent == 0) {
            q("UPDATE messages SET thread = " . $new . " WHERE id = " . $new);
        } else {
            q("UPDATE messages SET thread = " . $thread . " WHERE id = " . $new);
            q("UPDATE messages SET bumped = " . $now . " WHERE id = " . $thread);
        }

        // Redirect so that reloading the next page does not post twice.
        header("Location: read.php3?id=" . $new);
        exit;
    }
}

$PAGE_TITLE = ($parent == 0) ? "Post a Message" : "Post a Followup";
include("header.inc");
?>

<FONT FACE="Arial,Helvetica" SIZE="3"><B><? print($PAGE_TITLE) ?></B></FONT>
<BR><BR>

<?
if (count($errors) > 0) {
    print("<TABLE WIDTH=\"100%\" BORDER=\"1\" CELLPADDING=\"6\" CELLSPACING=\"0\" "
        . "BGCOLOR=\"#FFEEEE\"><TR><TD><FONT FACE=\"Arial,Helvetica\" SIZE=\"2\" "
        . "COLOR=\"#CC0000\"><B>Your message was not posted:</B><BR>\n");
    reset($errors);
    while (list(, $e) = each($errors)) {
        print("&#149; " . esc($e) . "<BR>\n");
    }
    print("</FONT></TD></TR></TABLE><BR>\n");
}

if ($parent != 0) {
    $res = q("SELECT subject, author FROM messages WHERE id = " . $parent);
    if ($prow = mysql_fetch_array($res)) {
        print("<FONT FACE=\"Arial,Helvetica\" SIZE=\"2\">In reply to <B>"
            . esc($prow["subject"]) . "</B> by " . esc($prow["author"])
            . ".</FONT><BR><BR>\n");
    }
}
?>

<FORM ACTION="post.php3" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="parent" VALUE="<? print($parent) ?>">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0">
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">Name:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="author" SIZE="40" MAXLENGTH="40"
             VALUE="<? print(esc($author)) ?>">
      <FONT FACE="Arial,Helvetica" SIZE="1">(required)</FONT></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">E-Mail:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="email" SIZE="40" MAXLENGTH="80"
             VALUE="<? print(esc($email)) ?>">
      <FONT FACE="Arial,Helvetica" SIZE="1">(optional)</FONT></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">Homepage:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="homepage" SIZE="40" MAXLENGTH="120"
             VALUE="<? print(esc($homepage)) ?>">
      <FONT FACE="Arial,Helvetica" SIZE="1">(optional)</FONT></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT"><FONT FACE="Arial,Helvetica" SIZE="2">Subject:</FONT></TD>
  <TD><INPUT TYPE="TEXT" NAME="subject" SIZE="50" MAXLENGTH="100"
             VALUE="<? print(esc($subject)) ?>"></TD>
</TR>
<TR>
  <TD ALIGN="RIGHT" VALIGN="TOP"><FONT FACE="Arial,Helvetica" SIZE="2">Comments:</FONT></TD>
  <TD><TEXTAREA NAME="body" ROWS="12" COLS="55" WRAP="VIRTUAL"><? print(esc($body)) ?></TEXTAREA></TD>
</TR>
<TR>
  <TD></TD>
  <TD>
    <INPUT TYPE="SUBMIT" VALUE="Submit Message">
    <INPUT TYPE="RESET" VALUE="Reset">
  </TD>
</TR>
</TABLE>
</FORM>

<FONT FACE="Arial,Helvetica" SIZE="1">
<B>Formatting:</B>
[b]bold[/b] &nbsp; [i]italic[/i] &nbsp; [url]http://www.example.com/[/url] &nbsp;
[email]you@host.com[/email] &nbsp; [quote]quoted text[/quote]<BR>
HTML tags are printed as text, not as markup. Please do not post other
people's e-mail addresses.
</FONT>

<? include("footer.inc") ?>
