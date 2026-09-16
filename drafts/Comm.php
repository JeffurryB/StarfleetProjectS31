<?php
// index.php
require_once 'session.php'; // This handles authorization automatically

// Initialize our admin privilege tracking variable
$is_admin = false;

// CHECK STRICT LEVEL 9 PRIVILEGES AGAINST THE 'ID' FIELD
$sql_auth = "SELECT ID FROM accounts WHERE username = ? LIMIT 1";
if ($stmt_auth = $conn->prepare($sql_auth)) {
    $stmt_auth->bind_param("s", $login_session);
    $stmt_auth->execute();
    $res_auth = $stmt_auth->get_result();
    
    if ($res_auth && $res_auth->num_rows == 1) {
        $auth_data = $res_auth->fetch_assoc();
        if ((int)$auth_data['ID'] === 1) {
            $is_admin = true; // Admin clearance verified
        }
    }
    $stmt_auth->close();
}

// Map your session variable cleanly into the HTML template
$active_user = htmlspecialchars($login_session, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LCARS // SECURE COM-LINK</title>
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=Antonio:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- LCARS PALETTE & STYLES --- */
        :root {
            --lcars-orange: #ff9900;
            --lcars-purple: #cc99cc;
            --lcars-blue: #99ccff;
            --lcars-red: #cc6666;
            --lcars-bg: #000000;
        }

       body {
    background-color: var(--lcars-bg);
    color: #ffffff;
    font-family: 'Antonio', sans-serif;
    text-transform: uppercase;
    margin: 0;
    padding: 20px;
    box-sizing: border-box;
    /* 🔒 Locks the entire page window in place */
    height: 100vh;
    overflow: hidden; 
    letter-spacing: 1px;
}

        /* --- UPGRADED 3-COLUMN LCARS MATRIX --- */
.lcars-container {
    display: grid;
    grid-template-columns: 180px 1fr 200px;
    /* Header takes 60px, the rest takes up exactly the remaining vertical space */
    grid-template-rows: 60px calc(100vh - 120px);
    gap: 20px;
    height: calc(100vh - 40px); /* Account for body 20px padding top/bottom */
}

/* Header spans across all 3 grid columns */
.lcars-header {
    grid-column: 1 / span 3;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    border-bottom: 4px solid var(--lcars-purple);
    padding-bottom: 5px;
}

/* Sidebar controls stay locked on column 1 */
.lcars-left-bar {
    grid-column: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Main Chat Terminal expands across column 2 */
.lcars-terminal {
    grid-column: 2;
    display: flex;
    flex-direction: column;
    border-left: 4px solid var(--lcars-purple);
    padding-left: 20px;
    min-width: 0;
    /* Tells this frame to fill its exact grid track without inflating it */
    height: 100%;
}

/* Active Roster stays locked on column 3 */
.lcars-right-bar {
    grid-column: 3;
    display: flex;
    flex-direction: column;
    border-left: 4px solid var(--lcars-orange);
    padding-left: 15px;
    background: rgba(255, 153, 0, 0.01);
}

/* Input Tray inside the terminal now expands naturally */
.chat-input-tray {
    display: flex;
    gap: 10px;
    width: 100%;
}

.chat-input-tray input[type="text"] {
    background-color: #111;
    border: 2px solid var(--lcars-blue);
    color: #fff;
    padding: 12px;
    font-family: 'Antonio', sans-serif;
    font-size: 1.2rem;
    flex-grow: 1; /* Instructs text box to stretch completely across space */
}

.lcars-panel-title {
    color: var(--lcars-orange);
    font-size: 1.2rem;
    font-weight: bold;
    border-bottom: 2px solid var(--lcars-orange);
    padding-bottom: 5px;
    margin-bottom: 15px;
}

.online-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    overflow-y: auto;
}

/* LCARS standard status capsule badges */
.user-badge-online {
    background-color: rgba(255, 153, 0, 0.1);
    border-left: 6px solid var(--lcars-orange);
    color: #fff;
    padding: 8px 12px;
    font-size: 1.05rem;
    font-weight: bold;
    border-radius: 0 4px 4px 0;
}

        .lcars-header {
            grid-column: 1 / span 2;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            border-bottom: 4px solid var(--lcars-purple);
            padding-bottom: 5px;
        }

        .lcars-header h1 {
            margin: 0;
            font-size: 2.2rem;
            color: var(--lcars-orange);
        }

        .lcars-stardate {
            color: var(--lcars-blue);
            font-size: 1.2rem;
        }

        .lcars-left-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .lcars-btn {
            background-color: var(--lcars-blue);
            color: #000;
            border: none;
            padding: 12px;
            font-family: 'Antonio', sans-serif;
            font-size: 1.1rem;
            font-weight: bold;
            text-align: right;
            border-radius: 20px 0 0 20px;
            cursor: pointer;
        }

        .lcars-btn.orange { background-color: var(--lcars-orange); }
        .lcars-btn.red { background-color: var(--lcars-red); }
        .lcars-btn.purple { background-color: var(--lcars-purple); }

        .lcars-spacer {
            background-color: var(--lcars-purple);
            flex-grow: 1;
            width: 35px;
            align-self: flex-end;
            border-radius: 10px 0 0 0;
        }

        .lcars-terminal {
            display: flex;
            flex-direction: column;
            border-left: 4px solid var(--lcars-purple);
            padding-left: 20px;
        }

        .chat-display {
    flex-grow: 1;
    background: rgba(153, 204, 255, 0.03);
    border: 2px solid #222;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
    font-size: 1.1rem;
    
    /* Enables scrolling on just the messages window */
    overflow-y: auto; 
}

        .msg-line {
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .msg-timestamp { color: var(--lcars-purple); margin-right: 8px;}
        .msg-user { color: var(--lcars-orange); font-weight: bold; margin-right: 8px;}
        .msg-text { color: #ffffff; text-transform: none; }

        .chat-input-tray {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-bottom: 5px; /* Tiny buffer from the layout edge */
}

        .lcars-user-badge {
            background-color: #222;
            border: 2px solid var(--lcars-purple);
            color: var(--lcars-orange);
            padding: 12px 20px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 15px 0 0 15px;
            display: flex;
            align-items: center;
            min-width: 120px;
            justify-content: center;
        }

        .chat-input-tray input[type="text"] {
            background-color: #111;
            border: 2px solid var(--lcars-blue);
            color: #fff;
            padding: 12px;
            font-family: 'Antonio', sans-serif;
            font-size: 1.2rem;
            flex-grow: 1;
        }

        #send-btn {
            background-color: var(--lcars-orange);
            color: #000;
            border: none;
            padding: 0 30px;
            font-family: 'Antonio', sans-serif;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            border-radius: 0 15px 15px 0;
        }
        
        /* Custom LCARS Track Styling */
.chat-display::-webkit-scrollbar {
    width: 8px;
}
.chat-display::-webkit-scrollbar-track {
    background: #000000;
}
.chat-display::-webkit-scrollbar-thumb {
    background: var(--lcars-purple);
    border-radius: 4px;
}
.chat-display::-webkit-scrollbar-thumb:hover {
    background: var(--lcars-orange);
}
    </style>
</head>
<body>

   <div class="lcars-container">
    <!-- 1. HEADER ARCH -->
    <header class="lcars-header">
        <h1>LCARS // SECURE COM-LINK</h1>
        <div class="lcars-stardate">STARDATE: <?php echo date("Ymd.H"); ?></div>
    </header>

    <!-- 2. LEFT SIDEBAR PANEL -->
    <aside class="lcars-left-bar">
        <div class="lcars-btn orange">SYS STATUS</div>
        <div class="lcars-btn purple">COM CH 01</div>
        <div class="lcars-btn">SECURE LN</div>
        <div class="lcars-btn red" onclick="clearDisplay()">CLR LOGS</div>
        <!-- LEVEL 9 SYSTEM ADMINISTRATOR EXCLUSIVE INTERFACE -->
    <?php if ($is_admin): ?>
        <div class="lcars-btn red" onclick="executeDatabaseWipe()">DB PURGE</div>
    <?php endif; ?>
        <div class="lcars-spacer"></div>
    </aside>

    <!-- 3. MAIN TERMINAL (Chat space expands cleanly here) -->
    <main class="lcars-terminal">
        <div class="chat-display" id="chatDisplay">
            <div class="msg-line">
                <span class="msg-timestamp">[SYSTEM]</span>
                <span class="msg-user">SECURE CHANNEL:</span>
                <span class="msg-text">Identity verified. Welcome back, <?php echo $active_user; ?>.</span>
            </div>
        </div>

        <!-- CHAT CONTROLS -->
        <form id="chatForm" class="chat-input-tray" onsubmit="sendMessage(event)">
            <div class="lcars-user-badge"><?php echo $active_user; ?></div>
            <input type="text" id="message" name="message" placeholder="ENTER QUANTUM DATA TRANSMISSION..." required autocomplete="off">
            <button type="submit" id="send-btn">SEND</button>
        </form>
    </main>

    <!-- 4. RIGHT ACTIVE ROSTER SIDEBAR (Now outside and sitting on the correct edge) -->
    <aside class="lcars-right-bar">
        <div class="lcars-panel-title">ACTIVE ROSTER</div>
        <div class="online-list" id="onlineList">
            <!-- User capsule badges will render dynamically here -->
            <div class="user-badge-online">SCANNING...</div>
        </div>
    </aside>
</div>

    <script>
        const chatDisplay = document.getElementById('chatDisplay');

        function fetchMessages() {
    fetch('fetch_messages.php')
        .then(response => response.json())
        .then(data => {
            // Unpack and print messages
            let chatHtml = '';
            data.messages.forEach(msg => {
                chatHtml += `
                    <div class="msg-line">
                        <span class="msg-timestamp">[${msg.chat_time}]</span>
                        <span class="msg-user">${msg.username}:</span>
                        <span class="msg-text">${msg.message}</span>
                    </div>
                `;
            });
            
            const shouldScroll = chatDisplay.scrollTop + chatDisplay.clientHeight >= chatDisplay.scrollHeight - 50;
            chatDisplay.innerHTML = chatHtml;
            if (shouldScroll) {
                chatDisplay.scrollTop = chatDisplay.scrollHeight;
            }

            // Unpack and print the online roster manifest
            let rosterHtml = '';
            data.online_users.forEach(user => {
                rosterHtml += `<div class="user-badge-online">${user}</div>`;
            });
            document.getElementById('onlineList').innerHTML = rosterHtml;
        })
        .catch(err => console.error("Sub-space retrieval error:", err));
}

        function sendMessage(e) {
            e.preventDefault();
            const form = document.getElementById('chatForm');
            const formData = new FormData(form);

            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('message').value = ''; 
                    fetchMessages();
                } else {
                    alert(data.message);
                }
            });
        }
        
         // EXCLUSIVE ADMINISTRATOR COMMAND INTERFACE
    function executeDatabaseWipe() {
    if (confirm("LCARS WARNING: THIS COMMAND WILL COMPLETELY VAPORIZE ALL CHAT LOG HISTORIES. CONFIRM SYSTEM PURGE?")) {
        
        // Pointing directly to the trusted log fetch channel with an action variable flag
        fetch('fetch_messages.php?action=purge', { method: 'POST' })
            .then(response => {
                if (!response.ok) {
                    throw new Error("HTTP Server Error Status " + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    alert("LCARS NOTIFICATION: Database cache wiped cleanly.");
                    fetchMessages(); // Force live viewport to render fresh empty list
                } else {
                    alert("ACCESS DENIED: " + data.message);
                }
            })
            .catch(err => {
                alert("CRITICAL TRANSMISSION FAULT: Authorization handshake blocked.");
                console.error("Purge failure breakdown:", err);
            });
    }
}

        function clearDisplay() {
            chatDisplay.innerHTML = '';
        }

        setInterval(fetchMessages, 2000);
        fetchMessages();
    </script>
</body>
</html>
