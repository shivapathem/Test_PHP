USE [Allocate7]
GO

INSERT INTO [dbo].[SicknessReason]
           ([SicknessName],[ReasonCode],[IsActive],[CreateBy],[CreatedDate])
     VALUES  ('Acute Limb Sprain','SR01',1,6 ,GETDATE()),('Anxiety','SR02',1,6 ,GETDATE()),
	 ('Burns,Poisoning','SR03',1,6 ,GETDATE()),('Cancer related','SR04',1,6 ,GETDATE()),('Cough/Cold/URTI','SR05',1,6 ,GETDATE()),
	 ('Dental','SR06',1,6 ,GETDATE()),('Depression','SR07',1,6 ,GETDATE()),('Ear, Nose & Throat','SR08',1,6 ,GETDATE()),
	 ('Endocrine/Glandular','SR09',1,6 ,GETDATE()), ('Eye problems','SR10',1,6 ,GETDATE()), ('Flu/Influenza','SR11',1,6 ,GETDATE()),
	  ('Food Poisoning','SR12',1,6 ,GETDATE()), ('Fracture/Ligament','SR13',1,6 ,GETDATE()), ('Frostbite,Hypotherm','SR14',1,6 ,GETDATE()), 
	  ('Gastro-enteritis','SR15',1,6 ,GETDATE()),
	   ('Gastrointestinal','SR16',1,6 ,GETDATE()), ('Genitourinary','SR17',1,6 ,GETDATE()), ('Gynaecological','SR18',1,6 ,GETDATE()), ('Headache/Migraine','SR19',1,6 ,GETDATE()), ('Heart & Circulatory','SR20',1,6 ,GETDATE()),
	    ('Infectious diseases','SR21',1,6 ,GETDATE()), ('Lower Back Pain','SR22',1,6 ,GETDATE()),
		('Mental Health','SR23',1,6 ,GETDATE()),('Musculoskeletal','SR24',1,6 ,GETDATE()),
		('Nervous System','SR25',1,6 ,GETDATE()),('Other','SR26',1,6 ,GETDATE()),('Pneumonia/Pleurisy','SR27',1,6 ,GETDATE())
		,('Pregnancy related','SR28',1,6 ,GETDATE()),('Reason Withheld','SR29',1,6 ,GETDATE()),('Respiratory','SR30',1,6 ,GETDATE())
		,('Sickness / Diarrhoea','SR31',1,6 ,GETDATE()),('Sinusitis','SR32',1,6 ,GETDATE()),('Skin disorder','SR33',1,6 ,GETDATE())
		,('Stress','SR34',1,6 ,GETDATE()),('Substance Related','SR35',1,6 ,GETDATE()),('Surgery','SR36',1,6 ,GETDATE())
		,('Viral Disease','SR37',1,6 ,GETDATE()),('Work-Anxiety','SR38',1,6 ,GETDATE()),('Work-Depression','SR39',1,6 ,GETDATE())
		,('Work-infect disease','SR40',1,6 ,GETDATE()),('Work-Mental Health','SR41',1,6 ,GETDATE()),('Work-Musculoskeletal','SR42',1,6 ,GETDATE())
		,('Work-Other','SR43',1,6 ,GETDATE()),('Work-Physical','SR44',1,6 ,GETDATE()),('Work-Respiratory','SR45',1,6 ,GETDATE())
		,('Work-Skin Problems','SR46',1,6 ,GETDATE()),('Work-Stress','SR47',1,6 ,GETDATE()),('Long Covid','SR48',1,6 ,GETDATE()),
		('Covid','SR49',1,6 ,GETDATE())


GO


