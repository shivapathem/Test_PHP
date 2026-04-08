USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocations_jobs]    Script Date: 11/10/2021 16:06:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

Alter table Allocations_Jobs_Publish alter column contact nvarchar(50)
Alter table Allocations_Jobs_Publish alter column location nvarchar(max)

GO