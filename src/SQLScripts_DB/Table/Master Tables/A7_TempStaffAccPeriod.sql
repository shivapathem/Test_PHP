USE [Allocate7]
GO
/****** Object:  Table [dbo].[StaffConfig]    Script Date: 09/09/2022 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'TempStaffAccPeriod')
	BEGIN
		CREATE TABLE dbo.TempStaffAccPeriod
		(
			NetLogin nvarchar(25),
			StaffId int,
			StartWeek int,
			EndWeek int,
			StartDate datetime,
			EndDate datetime,
			Period int,
			AccGroupID int

		)
	END
GO