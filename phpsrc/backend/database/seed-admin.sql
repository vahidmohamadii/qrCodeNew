INSERT INTO app_users (
  full_name,
  email,
  password_hash,
  role,
  is_active,
  created_at,
  updated_at
)
VALUES (
  'Administrator',
  'admin@example.com',
  '$2y$10$5imTabKo5wjL7ka0zsLN1eLurl9eiTzchrOCiaA8fk.JJRSO4yyV6',
  'Admin',
  1,
  UTC_TIMESTAMP(),
  UTC_TIMESTAMP()
)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  is_active = VALUES(is_active),
  updated_at = VALUES(updated_at);
