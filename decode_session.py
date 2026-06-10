import re
import json
import base64
import zlib

with open(r'd:\Download\pi-session-2026-06-10T02-46-54-981Z_019eaf6c-f905-7dc5-b76b-7263164672c7.html', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

with open('session_output.txt', 'w', encoding='utf-8') as out:
    match = re.search(r'<script id="session-data"[^>]*>(.+?)</script>', content, re.DOTALL)
    if match:
        encoded = match.group(1)
        try:
            decoded = base64.b64decode(encoded)
            try:
                decompressed = zlib.decompress(decoded)
                data = json.loads(decompressed)
            except:
                data = json.loads(decoded)
            
            if 'entries' in data:
                out.write(f"Total entries: {len(data['entries'])}\n")
                for i, entry in enumerate(data['entries']):
                    if entry.get('type') == 'message':
                        msg = entry.get('message', {})
                        if msg.get('role') == 'user':
                            content_list = msg.get('content', [])
                            for c in content_list:
                                if c.get('type') == 'text':
                                    out.write(f"\n=== USER MESSAGE {i} ===\n")
                                    out.write(c.get('text', '') + '\n')
                        elif msg.get('role') == 'assistant':
                            content_list = msg.get('content', [])
                            for c in content_list:
                                if c.get('type') == 'text' and c.get('text', '').strip():
                                    out.write(f"\n=== ASSISTANT MESSAGE {i} ===\n")
                                    out.write(c.get('text', '') + '\n')
                                elif c.get('type') == 'thinking':
                                    out.write(f"\n=== ASSISTANT THINKING {i} ===\n")
                                    out.write(c.get('thinking', '')[:800] + '\n')
                    elif entry.get('type') == 'custom_message':
                        custom = entry.get('content', '')
                        if custom:
                            out.write(f"\n=== CUSTOM MESSAGE {i} ===\n")
                            out.write(custom[:500] + '\n')
        except Exception as e:
            out.write(f"Error: {e}\n")

print("Done. Check session_output.txt")
