SET NAMES utf8mb4;
START TRANSACTION;
-- users
INSERT IGNORE INTO `users` (`username`, `password`, `userid`, `userlevel`, `email`, `timestamp`, `gali_skelbti`) VALUES
('Administratorius', '16c354b68848cdbd8f54a226a0a55b21', 'a2fe399900de341c39c632244eaf8483', 9, 'demo@ktu.lt', '2026-11-04 22:06:14', 1),
('rimas', 'c2acd92812ef99acd3dcdbb746b9a434', '689e5b2971577d707becb97405ede951', 9, 'rimas@litnet.lt', '2024-07-02 20:49:33', 0),
('jonas', '64067822105b320085d18e386f57d89a', '9c5ddd54107734f7d18335a5245c286b', 255, 'rimas@litnet.lt', '2017-05-09 17:10:37', 0),
('pranas', '16c354b68848cdbd8f54a226a0a55b21', 'aa69001f0ba493bed7dddfd1cdb06591', 4, 'pranas@ltu.ee', '2024-07-02 18:48:36', 0),
('kostas', '1c37511487d38c3ebc4c59650ce2d65a', '69986045e0925262d43addddaf76094f', 5, 'eeee@ll.lt', '2018-02-16 16:04:35', 0),
('stud', 'c2acd92812ef99acd3dcdbb746b9a434', 'de2cde3560412df8be3d2773e9f1a8b3', 4, 'stud@ktu.lt', '2026-11-04 22:17:57', 1);

-- skelbimas
INSERT IGNORE INTO `skelbimas` (`skelbimas_id`, `autorius_id`, `tipas`, `pavadinimas`, `rusis`, `lytis`, `amzius_men`, `vieta`, `aprasymas`, `galioja_iki`, `busena`, `sukurtas_at`) VALUES
(1, 'de2cde3560412df8be3d2773e9f1a8b3', 'dinges', 'Dingo reksas', 'suo', 'patinas', 16, 'Kaunas, Dainavos rajonas', 'Dingo reksiukas, vokieciu aviganis', '2026-12-04', 'aktyvus', '2026-11-04 21:19:10'),
(3, 'de2cde3560412df8be3d2773e9f1a8b3', 'rastas', 'Radau vokieciu avigani', 'suo', 'patinas', NULL, 'Kaunas centras', 'priglaudziau 060394250', NULL, 'aktyvus', '2026-11-04 21:53:07'),
(5, 'a2fe399900de341c39c632244eaf8483', 'dinges', 'asda', 'asd', 'patinas', 1, 'asd', 'asd', NULL, 'pasalintas', '2026-11-04 22:17:05');

-- nuotrauka
INSERT IGNORE INTO `nuotrauka` (`nuotrauka_id`, `skelbimas_id`, `failo_kelias`, `eiles_nr`, `ikelta_at`) VALUES
(1, 3, 'uploads/7d4200d17a6aeceefbb66a4847139140.webp', 1, '2026-11-04 21:53:07');

-- perziura
INSERT IGNORE INTO `perziura` (`perziura_id`, `skelbimas_id`, `naudotojas_id`, `perziureta_at`) VALUES
(1, 3, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:16:06'),
(2, 5, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:07'),
(3, 5, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:12'),
(4, 3, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:14'),
(5, 5, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:21'),
(6, 3, 'de2cde3560412df8be3d2773e9f1a8b3', '2026-11-04 22:18:46');

-- zinute
INSERT IGNORE INTO `zinute` (`zinute_id`, `skelbimas_id`, `siuntejas_id`, `gavejas_id`, `tekstas`, `issiusta_at`, `perskaityta_at`) VALUES
(1, 3, 'a2fe399900de341c39c632244eaf8483', 'de2cde3560412df8be3d2773e9f1a8b3', 'labas ar su raudonu antkakliu?', '2026-11-04 22:05:51', NULL),
(2, 3, 'de2cde3560412df8be3d2773e9f1a8b3', 'a2fe399900de341c39c632244eaf8483', 'labas, taip', '2026-11-04 22:06:10', NULL);

COMMIT;