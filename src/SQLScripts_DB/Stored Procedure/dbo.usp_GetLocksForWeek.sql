USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetLocksForWeek]    Script Date: 26/04/2022 16:12:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_GetLocksForWeek]
	-- Add the parameters for the stored procedure here
	@intStartWeek int, 
	@intEndWeek int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	SELECT LockRequests.iDay, LockRequests.ID, isnull(LockRequests.AllocationsInformation, '') as AllocationsInfo,LockRequests.ScheduledPersonID
	FROM LockRequests (Nolock)
	WHERE  
	(LockRequests.WeekNumber BETWEEN @intStartWeek AND @intEndWeek)
	AND (LockRequests.deleted = 0)

END

