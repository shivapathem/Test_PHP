USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveTypeAndGroupFromID]    Script Date: 13/06/2022 15:56:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_LeaveTypeAndGroupFromID]
	-- Add the parameters for the stored procedure here
	@LeaveTypeID int
AS
BEGIN

SELECT      LeaveRequestGroups.ID, LeaveRequestGroups.Description AS GroupDescription, leave_types.description AS TypeDescription
               FROM           LeaveRequestGroups (nolock)
               INNER JOIN     leave_types (nolock) ON LeaveRequestGroups.ID = leave_types.GroupID
               WHERE          (leave_types.ID = @LeaveTypeID);


END