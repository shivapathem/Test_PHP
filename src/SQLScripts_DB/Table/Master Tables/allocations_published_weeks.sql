USE [Allocate7]
GO
/****** Object:  Table [dbo].[adhoc_duty]    Script Date: 16/09/2021 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF  EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[Allocations_published_weeks]'))
drop TABLE [dbo].[Allocations_published_weeks]

GO
CREATE TABLE [dbo].[Allocations_published_weeks](
	[WeekNumber] [int],
    [SchedulingTeamID] [int] NOT NULL,
	[UpdatedBy] [varchar](50),
	[UpdatedDate] [datetime],
)

GO
