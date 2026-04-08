USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_updateWorktimeDirective]    Script Date: 18/08/2022 21:00:52 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE  [dbo].[usp_updateWorktimeDirective]
@teamId			           INT,
@SchedulingPersonID        INT, 
@dutyDate                  VARCHAR(22)

AS
BEGIN

    SET NOCOUNT ON

	UPDATE Working_time_directive
	   SET isapproved = 2
	 WHERE schedulingpersonid = @SchedulingPersonID
	   AND breachtype IN ( 1, 3, 4 )
	   AND CONVERT(DATETIME, @dutyDate, 102) BETWEEN startdate AND enddate 

END