USE [Allocate7]
GO
/****** Object:  Table [dbo].[adhoc_duty]    Script Date: 16/09/2021 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF  EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[adhoc_duty]'))
drop TABLE [dbo].[adhoc_duty]

GO
CREATE TABLE [dbo].[adhoc_duty](
    [AdhocID] [int] NOT NULL IDENTITY(1,1)  PRIMARY KEY,
    [StaffID] [int] NOT NULL,
    [StartDate] [datetime] NOT NULL,
    [EndDate] [datetime] NOT NULL,
	[StartTime] [int],
	[EndTime] [int],
    [DutyName] [varchar](50) NOT NULL,
    [SchedulingTeamID] [int] NOT NULL,
	[BackgroundColour] [varchar](50),
	[FontColour] [varchar](50),
    [DefaultColour] [varchar](50),
    [Comment] [varchar](255),
	[UpdatedBy] [varchar](50),
	[UpdatedDate] [datetime],
	[isActive] [bit] DEFAULT 1,
	[isEdited] [bit] DEFAULT 0
)

GO
