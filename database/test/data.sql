-- Insert de prueba de users


INSERT INTO users (name, email, email_verified_at, password, remember_token, created_at, updated_at) VALUES
('Carlos Mendoza', 'c.mendoza@empresa.com', '2025-04-09 08:00:00', '$2y$12$7b0f9ZnK.UKQ6Z5aNQYFcXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'XAF4SqP78RtY', '2025-04-09 08:00:00', '2025-04-09 08:00:00'),
('Ana Lopez', 'a.lopez@empresa.com', '2025-04-10 10:20:00', '$2y$12$917fA5bmL.LpQAv3zDvZQ3uHc5YhWjUY1Pp.O2wH1BczxCV1dfef0h', 'Lmb89vQ34KsT', '2025-04-10 10:20:00', '2025-04-10 10:20:00'),
('Pedro Ramírez', 'p.ramirez@empresa.com', NULL, '$2y$12$3RtY67v8.hmkJUB1PrdQQcXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'Qwf7af745ThM', '2025-04-11 11:45:00', '2025-04-11 11:45:00'),
('María González', 'm.gonzalez@empresa.com', '2025-04-12 09:10:00', '$2y$12$5YhMB9bV.CxZ6sDf6D132cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'Z3Xd5cV67hM', '2025-04-12 09:10:00', '2025-04-12 09:10:00'),
('José Martínez', 'j.martinez@empresa.com', NULL, '$2y$12$8KIP34qM.Er1y7u1fbw82cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'Rtf89yH67NL', '2025-04-13 14:00:00', '2025-04-13 14:00:00'),
('Laura Díaz', 'l.diaz@empresa.com', '2025-04-14 07:30:00', '$2y$12$2bE4S5FT.Yu18oPqAxX12cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'PMG34jH89LnB', '2025-04-14 07:30:00', '2025-04-14 07:30:00'),
('Ricardo Soto', 'r.soto@empresa.com', '2025-04-15 12:15:00', '$2y$12$4Qz67x5.Dcv9bNml.pk32cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'Vg85chV78KdO', '2025-04-15 12:15:00', '2025-04-15 12:15:00'),
('Fernanda Castro', 'f.castro@empresa.com', NULL, '$2y$12$6Ty0781K.JmH9nBv6cD12cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'CdE34fV67bM', '2025-04-16 16:40:00', '2025-04-16 16:40:00'),
('Diego Herrera', 'd.herrera@empresa.com', '2025-04-17 08:20:00', '$2y$12$1Ee74SyL.IoPqqbZXcV23cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'Xz57RaQd5TmM', '2025-04-17 08:20:00', '2025-04-17 08:20:00'),
('Sofía Vargas', 's.vargas@empresa.com', '2025-04-18 13:10:00', '$2y$12$9D1K34qQ.MeR7yTgbHM2aCxr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'KiJ28M667Fd5', '2025-04-18 13:10:00', '2025-04-18 13:10:00'),
('Jorge Medina', 'j.medina@empresa.com', NULL, '$2y$12$3Edc45rF.VgB67njHM12cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'Hg778yH67NL', '2025-04-19 09:45:00', '2025-04-19 09:45:00'),
('Valentina Ríos', 'v.rios@empresa.com', '2025-04-20 10:30:00', '$2y$12$7YhMB9bV.CxZ6sDf6D132cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'BwM67vGB9GL', '2025-04-20 10:30:00', '2025-04-20 10:30:00'),
('Andrés Silva', 'a.silva@empresa.com', '2025-04-21 14:15:00', '$2y$12$5RtF67vH.JuKB1pO2X12cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'P10981K67UNG', '2025-04-21 14:15:00', '2025-04-21 14:15:00'),
('Gabriela Morales', 'g.morales@empresa.com', '2025-04-22 11:20:00', '$2y$12$8KIP34qM.Er1y7u1fbw82cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'TgH65jK89LpO', '2025-04-22 11:20:00', '2025-04-22 11:20:00'),
('Oscar Fuentes', 'o.fuentes@empresa.com', NULL, '$2y$12$2bE4S5FT.Yu18oPqAxX12cXr6pLmMqFLoIyV.d2kHt8SvQAvT6Cd3S', 'VffR67nB89MM', '2025-04-23 09:45:00', '2025-04-23 09:45:00');

-- Insert de prueba de empleados

INSERT INTO empleados (usuario_id, salario_base, created_at, updated_at) VALUES
(1, 2800.00, '2025-04-07 04:30:00', '2025-04-07 04:30:00'),
(2, 3200.50, '2025-04-08 00:30:00', '2025-04-10 09:15:00'),
(3, 2450.75, '2025-04-09 08:00:00', '2025-04-09 08:00:00'),
(4, 3800.00, '2025-04-10 10:20:00', '2025-04-15 14:30:00'),
(5, 2950.25, '2025-04-11 11:45:00', '2025-04-11 11:45:00'),
(6, 4100.00, '2025-04-12 09:10:00', '2025-04-18 16:20:00'),
(7, 2250.50, '2025-04-13 14:00:00', '2025-04-13 14:00:00'),
(8, 3500.75, '2025-04-14 07:30:00', '2025-04-22 10:45:00'),
(9, 2650.00, '2025-04-15 12:15:00', '2025-04-15 12:15:00'),
(10, 3900.25, '2025-04-16 16:40:00', '2025-04-16 16:40:00'),
(11, 3100.00, '2025-04-17 08:20:00', '2025-04-20 11:30:00'),
(12, 2750.50, '2025-04-18 13:10:00', '2025-04-18 13:10:00'),
(13, 3350.75, '2025-04-19 09:45:00', '2025-04-19 09:45:00'),
(14, 4200.00, '2025-04-20 10:30:00', '2025-04-21 15:20:00'),
(15, 2400.25, '2025-04-21 14:15:00', '2025-04-21 14:15:00');

INSERT INTO pagos_empleados (empleado_id, periodo_mes, periodo_anio, tipo_estado_id, monto_base, monto_total, created_at, updated_at) VALUES
(1 , 4, 2025, 3, 2800.00, 3000.00, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),
(2 , 4, 2025, 3, 3200.50, 3500.50, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),
(3 , 4, 2025, 3, 2450.75, 2350.75, '2025-04-30 18:05:00', '2025-04-30 18:05:00'),
(4 , 4, 2025, 3, 3800.00, 3800.00, '2025-04-30 18:05:00', '2025-04-30 18:05:00'),
(5 , 4, 2025, 3, 2950.25, 3050.25, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),
(6 , 4, 2025, 3, 4100.00, 4400.00, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),
(7 , 4, 2025, 3, 2250.50, 1950.50, '2025-04-30 18:15:00', '2025-04-30 18:15:00'),
(8 , 4, 2025, 3, 3500.75, 3700.75, '2025-04-30 18:15:00', '2025-04-30 18:15:00'),
(9 , 4, 2025, 3, 2650.00, 2650.00, '2025-04-30 18:20:00', '2025-04-30 18:20:00'),
(10, 4, 2025, 3, 3900.25, 3950.25, '2025-04-30 18:20:00', '2025-04-30 18:20:00');

INSERT INTO ajustes_salariales (pago_empleado_id, tipo_ajuste_id, descripcion, monto, created_at, updated_at) VALUES
-- Pago 1 – Empleado 1
(1, 3 , 'Horas extra abril',              150.00, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),
(1, 2 , 'Bono puntualidad abril',         100.00, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),
(1, 7 , 'Descuento por atraso',            50.00, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),

-- Pago 2 – Empleado 2
(2, 3 , 'Horas extra proyecto X',         200.00, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),
(2, 4 , 'Bono productividad Q1',          100.00, '2025-04-30 18:00:00', '2025-04-30 18:00:00'),

-- Pago 3 – Empleado 3
(3, 7 , 'Descuento por atraso',           100.00, '2025-04-30 18:05:00', '2025-04-30 18:05:00'),

-- Pago 5 – Empleado 5
(5, 1 , 'Bono asistencia perfecto',       250.00, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),
(5,12 , 'Equipo dañado - laptop',         150.00, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),

-- Pago 6 – Empleado 6
(6, 3 , 'Horas extra guardia',            300.00, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),
(6, 4 , 'Bono productividad',             200.00, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),
(6, 8 , 'Descuento ausencia 1 día',       200.00, '2025-04-30 18:10:00', '2025-04-30 18:10:00'),

-- Pago 7 – Empleado 7
(7, 9 , 'Cuota préstamo personal',        300.00, '2025-04-30 18:15:00', '2025-04-30 18:15:00'),

-- Pago 8 – Empleado 8
(8, 2 , 'Bono puntualidad abril',         200.00, '2025-04-30 18:15:00', '2025-04-30 18:15:00'),

-- Pago 10 – Empleado 10
(10,3 , 'Horas extra mantenimiento',      150.00, '2025-04-30 18:20:00', '2025-04-30 18:20:00'),
(10,12, 'Equipo extraviado',              100.00, '2025-04-30 18:20:00', '2025-04-30 18:20:00');

--tipo productos
INSERT INTO tipo_productos (nombre, created_at, updated_at) VALUES
('Uniformes', NOW(), NOW()),
('Útiles Escolares', NOW(), NOW()),
('Otros', NOW(), NOW());
--Insert de productos
INSERT INTO productos (tipo_producto_id, nombre, fecha_ingreso, precio_unitario, cantidad, created_at, updated_at) VALUES
-- Uniformes
(1, 'Camisa Escolar Blanca', '2025-01-10', 45.00, 100, NOW(), NOW()),
(1, 'Pantalón Azul Marino', '2025-01-10', 60.00, 80, NOW(), NOW()),
(1, 'Suéter Escolar', '2025-02-05', 75.00, 50, NOW(), NOW()),
-- Útiles
(2, 'Cuaderno de Matemática', '2025-01-12', 15.00, 200, NOW(), NOW()),
(2, 'Lapicero Azul', '2025-01-15', 2.50, 300, NOW(), NOW()),
(2, 'Lápiz de Grafito', '2025-01-15', 1.50, 300, NOW(), NOW()),
(2, 'Mochila Escolar', '2025-01-20', 120.00, 40, NOW(), NOW()),
-- Otros
(3, 'Botella de Agua Reutilizable', '2025-02-01', 25.00, 60, NOW(), NOW()),
(3, 'Agenda Escolar', '2025-01-18', 20.00, 100, NOW(), NOW()),
(3, 'Carnet Estudiantil', '2025-02-10', 10.00, 150, NOW(), NOW());
