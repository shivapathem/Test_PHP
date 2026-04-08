USE [Allocate7]
GO
SET IDENTITY_INSERT [dbo].[REF_Forms] ON 

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Master Duty')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (1, N'Master Duty', N'Master Duty', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Master Job')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (2, N'Master Job', N'Master Job', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Rotas')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (3, N'Rotas', N'Rotas', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Master Duties Filter')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (4, N'Master Duties Filter', N'Master Duties Filter', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Miscalleneous Duties')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (5, N'Miscalleneous Duties', N'Miscalleneous Duties', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Scheduled Person')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (6, N'Scheduled Person', N'Scheduled Person', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Divisions')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (7, N'Divisions', N'Division', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Scheduling Team')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (8, N'Scheduling Team', N'Scheduling Team', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='AllocateUsers')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (9, N'AllocateUsers', N'Listing of scheduled people and non-scheduled people.', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Public Holidays')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (10, N'Public Holidays', N'Public Holidays', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Non-Scheduled Staff')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (11, N'Non-Scheduled Staff', N'Non-Scheduled Staff assigned team', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Shift Leaders')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (12, N'Shift Leaders', N'Shift Leaders', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='ExtraXmasPoint')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (13, N'ExtraXmasPoint', N'Extra Xmas Point By Scheduling team user', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Christmas Points')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (14, N'Christmas Points', N'Christmas Points', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Division Admin')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (15, N'Division Admin', N'Mapping of Admin from Division', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Master Duty Colours')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) VALUES (16, N'Master Duty Colours', N'Master Duty Colours', 1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='Charge Code')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) values (17,'Charge Code','Charge Code',1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='WBS Code')
              INSERT [dbo].[REF_Forms] ([FormID], [FormName], [FormDescription], [IsActive]) values (18,'WBS Code','WBS Code',1)

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='LeaveReport')
            INSERT into REF_Forms ([FormID], [FormName], [FormDescription], [IsActive]) values(21,'LeaveReport','Leave Report BY Divisional',1);

UPDATE REF_Forms SET IsActive=1 WHERE FormID=21

IF NOT EXISTS (SELECT 1 FROM [dbo].[REF_Forms] WHERE FormName='WtdReport')
            INSERT into REF_Forms ([FormID], [FormName], [FormDescription], [IsActive]) values(23,'WtdReport','WTD Report',1);

SET IDENTITY_INSERT [dbo].[REF_Forms] OFF


