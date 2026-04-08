USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_SumLeaveAllocation]    Script Date: 24/06/2022 17:08:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_SumLeaveAllocation]
@iYear INT,
@SchedulingPersonId  INT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


SELECT  SUM(ISNULL(LeaveAllocation.Annual, 0)) + SUM(ISNULL(LeaveAllocation.PHL, 0)) 
+ SUM(ISNULL(LeaveAllocation.Comp, 0)) + SUM(ISNULL(LeaveAllocation.TOIL, 0)) + SUM(ISNULL(LeaveAllocation.Casual, 0)) 
+ SUM(ISNULL(LeaveAllocation.Other, 0)) AS TotalLeave
FROM  LeaveAllocation (nolock)
WHERE (LeaveAllocation.iYear = @iYear) AND (LeaveAllocation.SchedulingPersonId = @SchedulingPersonId) AND
(LeaveAllocation.IsActive = 1);   


END
