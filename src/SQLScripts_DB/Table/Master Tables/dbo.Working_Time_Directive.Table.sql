USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

 IF NOT  EXISTS (SELECT 1 FROM sys.objects 
    WHERE object_id = OBJECT_ID(N'[dbo].[Working_Time_Directive]'))
  BEGIN
	CREATE TABLE [dbo].[Working_Time_Directive](
		[ID] INT IDENTITY(1,1) NOT NULL,
		[SchedulingTeamId] [int] NULL,
		[SchedulingPersonID] [int] NULL,
		[BreachType] [int] NULL,
		[StartDate] [datetime] NULL,
		[EndDate] [datetime] NULL,
		[BreachedBy] [varchar](100) NULL,
		[BreachedDate] [datetime] NULL,
		[IsApproved] [INT] NULL,
		[ApprovedBy] [varchar](100) NULL,
		[ApprovedDate] [datetime] NULL,
		[Comments] [VARCHAR](1000) NULL,
		[History] [VARCHAR](MAX) NULL
	) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
  END
GO

