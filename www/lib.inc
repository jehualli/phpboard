<?
//
// lib.inc -- shared routines. include() this at the top of every page,
// after config.inc.
//
// Written for PHP 3.0.x. That means no foreach, no session functions, no
// $_POST, no require_once and no === . If you are reading this in 2026 and
// something looks laborious, it is because the shortcut had not been
// invented yet.
//

//
// ------------------------------------------------------------------ 1998
// In 1998 php.ini shipped with register_globals = On, so a form field
// named "subject" simply turned up as $subject, and the long-form arrays
// $HTTP_POST_VARS / $HTTP_GET_VARS / $HTTP_COOKIE_VARS were there too.
// PHP 5.4 deleted both of those, so this block puts them back. It is the
// only part of this application that is not period code -- everything
// below the line is what you would actually have typed.
// -------------------------------------------------------------------------
//
$HTTP_GET_VARS    = $_GET;
$HTTP_POST_VARS   = $_POST;
$HTTP_COOKIE_VARS = $_COOKIE;
$PHP_SELF         = $_SERVER["PHP_SELF"];
$REQUEST_METHOD   = $_SERVER["REQUEST_METHOD"];
$REMOTE_ADDR      = $_SERVER["REMOTE_ADDR"];
$HTTP_USER_AGENT  = $_SERVER["HTTP_USER_AGENT"];
$HTTP_REFERER     = "";
if (isset($_SERVER["HTTP_REFERER"])) $HTTP_REFERER = $_SERVER["HTTP_REFERER"];

reset($_GET);
while (list($k, $v) = each($_GET))    { $GLOBALS[$k] = $v; }
reset($_POST);
while (list($k, $v) = each($_POST))   { $GLOBALS[$k] = $v; }
reset($_COOKIE);
while (list($k, $v) = each($_COOKIE)) { $GLOBALS[$k] = $v; }
// ------------------------------------------------------------------------

$LINK = 0;

//
// Read one submitted field.
//
// The obvious thing to write is:
//
//     $subject = "";
//     if (isset($subject)) ...
//
// which does not work, because with register_globals on, $subject and
// $GLOBALS["subject"] are the same variable -- so assigning the empty default
// throws away what the visitor typed. Reading $HTTP_POST_VARS / $HTTP_GET_VARS
// is how you avoid it, which is most of the reason careful code used the long
// arrays even when it did not have to.
//
function gpc($name, $default) {
    global $HTTP_POST_VARS, $HTTP_GET_VARS;
    if (isset($HTTP_POST_VARS[$name])) return $HTTP_POST_VARS[$name];
    if (isset($HTTP_GET_VARS[$name]))  return $HTTP_GET_VARS[$name];
    return $default;
}

function gpc_isset($name) {
    global $HTTP_POST_VARS, $HTTP_GET_VARS;
    if (isset($HTTP_POST_VARS[$name])) return 1;
    if (isset($HTTP_GET_VARS[$name]))  return 1;
    return 0;
}

//
// Connect to the database. Dies noisily, which is what everybody's board
// did when the MySQL server fell over.
//
function db_connect() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $LINK;

    $LINK = @mysql_connect($DB_HOST, $DB_USER, $DB_PASS);
    if (!$LINK) {
        print("<H2>Database error</H2>\n");
        print("Could not connect to the database server. Please try again ");
        print("in a few minutes.<BR>\n");
        print("<!-- " . mysql_error() . " -->\n");
        exit;
    }
    if (!@mysql_select_db($DB_NAME, $LINK)) {
        print("<H2>Database error</H2>\n");
        print("Could not select the database.<BR>\n");
        exit;
    }
}

//
// Run a query or die trying.
//
function q($sql) {
    $res = mysql_query($sql);
    if (!$res) {
        print("<PRE>SQL error: " . htmlspecialchars(mysql_error()) . "\n");
        print("Query was: " . htmlspecialchars($sql) . "</PRE>\n");
        exit;
    }
    return $res;
}

//
// Quote a string for use in a query. magic_quotes_gpc used to do half of
// this for you, and doing it twice was the most common bug on the web.
//
function sqlstr($s) {
    return "'" . addslashes($s) . "'";
}

function sqlint($s) {
    return (int) $s;
}

//
// ---------------------------------------------------------------- sessions
// PHP has no session support until version 4, so here is the usual
// arrangement: a random id in a cookie, and a serialized array in a table.
// -------------------------------------------------------------------------
//
$SESS_ID   = "";
$SESS_DATA = array();

function sess_start() {
    global $SESS_ID, $SESS_DATA, $SESS_LIFE, $REMOTE_ADDR;

    // Throw away whatever has gone stale. One in ten page views, because
    // doing it every time is a waste of the database's afternoon.
    if (rand(1, 10) == 1) {
        q("DELETE FROM sessions WHERE ts < " . (time() - $SESS_LIFE));
    }

    $sid = "";
    if (isset($GLOBALS["BOARDSESS"])) $sid = $GLOBALS["BOARDSESS"];
    if (!ereg("^[0-9a-f]{32}$", $sid)) $sid = "";

    if ($sid != "") {
        $res = q("SELECT data FROM sessions WHERE sid = " . sqlstr($sid));
        if ($row = mysql_fetch_array($res)) {
            $SESS_ID = $sid;
            $SESS_DATA = unserialize($row["data"]);
            if (!is_array($SESS_DATA)) $SESS_DATA = array();
            q("UPDATE sessions SET ts = " . time() . " WHERE sid = " . sqlstr($sid));
            return;
        }
    }

    // No session, or a stale one. Mint a new id.
    $SESS_ID = md5(uniqid(rand()));
    $SESS_DATA = array();
    setcookie("BOARDSESS", $SESS_ID, 0, "/");
    q("INSERT INTO sessions (sid, data, ts, ip) VALUES ("
      . sqlstr($SESS_ID) . ", '', " . time() . ", " . sqlstr($REMOTE_ADDR) . ")");
}

function sess_get($key) {
    global $SESS_DATA;
    if (isset($SESS_DATA[$key])) return $SESS_DATA[$key];
    return "";
}

function sess_set($key, $value) {
    global $SESS_DATA, $SESS_ID;
    $SESS_DATA[$key] = $value;
    q("UPDATE sessions SET data = " . sqlstr(serialize($SESS_DATA))
      . ", ts = " . time() . " WHERE sid = " . sqlstr($SESS_ID));
}

function sess_kill() {
    global $SESS_ID, $SESS_DATA;
    q("DELETE FROM sessions WHERE sid = " . sqlstr($SESS_ID));
    $SESS_DATA = array();
    setcookie("BOARDSESS", "", time() - 3600, "/");
}

function is_admin() {
    return sess_get("admin") == "1";
}

function who_online() {
    $res = q("SELECT COUNT(*) AS n FROM sessions WHERE ts > " . (time() - 300));
    $row = mysql_fetch_array($res);
    return $row["n"];
}

//
// -------------------------------------------------------------------- text
//

function esc($s) {
    return htmlspecialchars($s);
}

//
// UBB code, the way the Ultimate Bulletin Board did it. The alternative in
// 1998 was to allow raw HTML in posts, which is how every board of the era
// ended up with somebody's <BLINK> tag halfway down the front page.
//
function ubb($text) {
    $t = htmlspecialchars($text);

    $t = eregi_replace("\\[b\\](.*)\\[/b\\]", "<B>\\1</B>", $t);
    $t = eregi_replace("\\[i\\](.*)\\[/i\\]", "<I>\\1</I>", $t);
    $t = eregi_replace("\\[url\\](http[^]]*)\\[/url\\]",
                       "<A HREF=\"\\1\" TARGET=\"_blank\">\\1</A>", $t);
    $t = eregi_replace("\\[email\\]([^]]*)\\[/email\\]",
                       "<A HREF=\"mailto:\\1\">\\1</A>", $t);
    $t = eregi_replace("\\[quote\\](.*)\\[/quote\\]",
                       "<BLOCKQUOTE><FONT SIZE=\"2\"><I>\\1</I></FONT></BLOCKQUOTE>", $t);

    return nl2br($t);
}

//
// "14-Aug-98 22:14:03". The year is shifted so that the board reads as 1998
// no matter what the server clock says.
//
function fmt_date($ts) {
    $shift = date("Y") - 1998;
    return date("d-M-y H:i:s", mktime(date("H", $ts), date("i", $ts),
                                      date("s", $ts), date("m", $ts),
                                      date("d", $ts), date("Y", $ts) - $shift));
}

function fmt_ago($ts) {
    $secs = time() - $ts;
    if ($secs < 60)    return $secs . " sec ago";
    if ($secs < 3600)  return ((int) ($secs / 60)) . " min ago";
    if ($secs < 86400) return ((int) ($secs / 3600)) . " hrs ago";
    return ((int) ($secs / 86400)) . " days ago";
}

function trim_to($s, $n) {
    if (strlen($s) <= $n) return $s;
    return substr($s, 0, $n - 3) . "...";
}

//
// The hit counter. Every site had one and most of them lied.
//
function bump_counter() {
    q("UPDATE counter SET hits = hits + 1");
    $res = q("SELECT hits FROM counter");
    $row = mysql_fetch_array($res);
    return $row["hits"];
}

//
// Six digits of green-on-black, as the LED counter GIFs did it.
//
function counter_html($n) {
    $s = "" . $n;
    while (strlen($s) < 6) $s = "0" . $s;
    $out = "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" "
         . "BGCOLOR=\"#000000\"><TR>";
    for ($i = 0; $i < strlen($s); $i++) {
        $out .= "<TD BGCOLOR=\"#000000\"><FONT FACE=\"Courier New\" SIZE=\"2\" "
              . "COLOR=\"#00FF00\"><B>" . $s[$i] . "</B></FONT></TD>";
    }
    return $out . "</TR></TABLE>";
}
?>
